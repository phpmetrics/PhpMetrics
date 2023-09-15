<?php
declare(strict_types=1);

namespace Hal\Metric\Class_\Text;

use Closure;
use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Helper\NodeIteratorInterface;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use function array_map;
use function array_unique;
use function log;
use function max;
use function property_exists;
use function round;

/**
 * Calculates Halstead complexity
 *
 * According to Wikipedia, "Halstead complexity measures are software metrics introduced by Maurice Howard Halstead in
 * 1977 as part of his treatise on establishing an empirical science of software development.
 * Halstead makes the observation that metrics of the software should reflect the implementation or
 * expression of algorithms in different languages, but be independent of their execution on a specific platform.
 * These metrics are therefore computed statically from the code."
 *
 * The metrics defined by this visitor are:
 * - Number of operands (variables, properties, fixed values)
 * - Number of unique operands
 * - Number of operators (math operators, logical operators, assignments, ...)
 * - Number of unique operators
 * - Length: sum of the operators and operands.
 * - Vocabulary: sum of unique operators and operands.
 * - Volume: calculated from Length and Vocabulary. A Volume < 100 means an easily readable class.
 * - Difficulty: represent how hard it is to work on a class without introduce bugs. High value means high risk.
 * - Level: inverse of the difficulty. High Level means easy to work class.
 * - Effort: worth the Difficulty times the Volume.
 * - Time: estimated time in seconds to implement a class, according to the Effort.
 * - Bugs: estimated number of bugs encountered in a class, according to the Effort and the abilities of the developer
 *   of the class. As this latest value is purely subjective, Halstead defined 3000 as a good default value for the
 *   developer's ability. This value must be the closest to 0.
 * - Intelligence: worth the Volume times the Level. Small value for a class means dumb class. And dumb class means easy
 *   to understand as a human.
 */
final class HalsteadVisitor extends NodeVisitorAbstract
{
    /** @var array<int, string> */
    private static array $operands;
    /** @var array<int, string> */
    private static array $operators;

    /**
     * @param Metrics $metrics
     * @param NodeIteratorInterface $nodeIterator
     */
    public function __construct(
        private readonly Metrics $metrics,
        private readonly NodeIteratorInterface $nodeIterator,
    ) {
    }

    /**
     * Reset the list of operators and operands.
     * @return void
     */
    private static function resetOperatorsAndOperands(): void
    {
        self::$operands = [];
        self::$operators = [];
    }

    /**
     * Returns the list of all operators and operands
     * @return array{array<int, string>, array<int, string>}
     */
    private static function getOperatorsAndOperands(): array
    {
        return [self::$operators, self::$operands];
    }

    /**
     * Returns the list of unique operators and operands.
     * @return array{array<int, string>, array<int, string>}
     */
    private static function getUniqueOperatorsAndOperands(): array
    {
        $uniq = static function (array $elementsToDedupe): array {
            return array_map(unserialize(...), array_unique(array_map(serialize(...), $elementsToDedupe)));
        };

        /** @var array<int, string> $uniqueOperators */
        $uniqueOperators = $uniq(self::$operators);
        /** @var array<int, string> $uniqueOperands */
        $uniqueOperands = $uniq(self::$operands);
        return [$uniqueOperators, $uniqueOperands];
    }

    /**
     * {@inheritDoc}
     */
    public function leaveNode(Node $node): null|int|Node|array // TODO PHP 8.2: only return null here.
    {
        if (
            !$node instanceof Stmt\Class_
            && !$node instanceof Stmt\Function_
            && !$node instanceof Stmt\Trait_
            //TODO: && !$node instanceof Stmt\Enum_
        ) {
            return null;
        }

        $nodeName = ($node instanceof Stmt\Function_)
            ? MetricNameGenerator::getFunctionName($node)
            : MetricNameGenerator::getClassName($node);
        /** @var Metric $classOrFunction */
        $classOrFunction = $this->metrics->get($nodeName);

        // Search for operands and operators and calculate halstead metrics.
        self::resetOperatorsAndOperands();

        $this->nodeIterator->iterateOver($node, $this->getVisitorCallback());
        [$operators, $operands] = self::getOperatorsAndOperands();
        [$uniqueOperators, $uniqueOperands] = self::getUniqueOperatorsAndOperands();

        // Set default values.
        $volume = 0;
        $nbUniqueOperators = 0;
        $nbUniqueOperands = 0;
        $nbOperators = 0;
        $nbOperands = 0;
        $effort = 0;
        $difficulty = 0;
        $bugs = 0;
        $time = 0;
        $intelligentContent = 0;
        $level = 0;
        $length = 0;
        $vocabulary = 0;

        if ([] !== $operands) {
            $nbUniqueOperators = count($uniqueOperators);
            $nbUniqueOperands = count($uniqueOperands);
            $nbOperators = count($operators);
            $nbOperands = count($operands);

            $devAbility = 3000;
            $length = $nbOperators + $nbOperands;
            $vocabulary = $nbUniqueOperators + $nbUniqueOperands;
            $volume = $length * log($vocabulary, 2);
            $level = (2 / max(1, $nbUniqueOperators)) * ($nbUniqueOperands / $nbOperands);
            $difficulty = ($nbUniqueOperators / 2) * ($nbOperands / $nbUniqueOperands);
            $effort = $volume * $difficulty;
            $bugs = ($effort ** (2 / 3)) / $devAbility;
            $time = $effort / 18;
            $intelligentContent = $level * $volume;
        }

        // Save results.
        $classOrFunction->set('length', $length);
        $classOrFunction->set('vocabulary', $vocabulary);
        $classOrFunction->set('volume', round($volume, 2));
        $classOrFunction->set('difficulty', round($difficulty, 2));
        $classOrFunction->set('effort', round($effort, 2));
        $classOrFunction->set('level', round($level, 2));
        $classOrFunction->set('bugs', round($bugs, 2));
        $classOrFunction->set('time', round($time));
        $classOrFunction->set('intelligentContent', round($intelligentContent, 2));
        $classOrFunction->set('number_operators', $nbOperators);
        $classOrFunction->set('number_operands', $nbOperands);
        $classOrFunction->set('number_operators_unique', $nbUniqueOperators);
        $classOrFunction->set('number_operands_unique', $nbUniqueOperands);

        return null;
    }

    /**
     * Returns the callback that will calculate the Halstead metrics using each sub-node found while iterating over all
     * analysed statements.
     *
     * @return Closure
     */
    private function getVisitorCallback(): Closure
    {
        return static function (Node $node): void {
            if ($node instanceof Node\Param && $node->var instanceof Node\Expr\Variable) {
                return;
            }

            if (
                $node instanceof Node\Expr\BinaryOp
                || $node instanceof Node\Expr\AssignOp
                || $node instanceof Stmt\If_
                || $node instanceof Stmt\For_
                || $node instanceof Stmt\Switch_
                || $node instanceof Stmt\Catch_
                || $node instanceof Stmt\Return_
                || $node instanceof Stmt\While_
                || $node instanceof Node\Expr\Assign
            ) {
                // operators
                self::$operators[] = $node->getType();
            }

            if (
                $node instanceof Node\Expr\Cast
                || $node instanceof Node\Expr\Variable
                || $node instanceof Node\Param
                || $node instanceof Node\Scalar
            ) {
                // operands
                self::$operands[] = match (true) {
                    property_exists($node, 'value') => $node->value,
                    property_exists($node, 'name') => $node->name,
                    default => $node->getType(),
                };
            }
        };
    }
}
