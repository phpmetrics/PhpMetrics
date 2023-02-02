<?php
declare(strict_types=1);

namespace Hal\Metric\Class_\Structural;

use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Helper\NodeIteratorInterface;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use function array_map;
use function array_sum;
use function round;

/**
 * Calculates Card And Agresti metric
 *
 * This metrics calculates the system complexity of a design for a class. The design system complexity is defined as the
 * sum of the structural complexity (connexions between procedures or methods) plus the local complexity (internal
 * structure of each procedure or method).
 *
 * The structural complexity is calculated by S = (sum(fan-out^2) for each method) / number of methods in the class,
 * while the fan-out is the number of calls inside a given method. Fan-out are not counting internal calls, but only
 * calls to user-defined methods.
 * The local complexity is calculated by L = (sum(local method complexity) for each method) / number of methods in the
 * class. The local method complexity is calculated by Li = (nb. arguments + nb. distinct returns) / (fan-out + 1).
 * `$this` is ignored on dynamic calls as argument. If `return` is used but no value is returned, this statement is also
 * ignored.
 * Finally, the system complexity is calculated by C = S + L.
 *
 * The lowest value for the system complexity is the best. High value denotes a bad design quality of a class.
 *
 * @see https://www.witpress.com/Secure/elibrary/papers/SQM94/SQM94024FU.pdf
 */
final class SystemComplexityVisitor extends NodeVisitorAbstract
{
    /** @var array<int> */
    private array $structuralComplexityByMethod;
    /** @var array<float> */
    private array $localComplexityByMethod;

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
     * {@inheritDoc}
     */
    public function leaveNode(Node $node): null|int|Node|array // TODO PHP 8.2: only return null here.
    {
        if (
            !$node instanceof Stmt\Class_
            && !$node instanceof Stmt\Trait_
            //TODO: && !$node instanceof Stmt\Enum_
        ) {
            return null;
        }

        /** @var Metric $class */
        $class = $this->metrics->get(MetricNameGenerator::getClassName($node));
        $this->structuralComplexityByMethod = [];
        $this->localComplexityByMethod = [];

        array_map(function (Stmt\ClassMethod $stmt): void {
            $io = count($stmt->getParams());
            $fanOut = 0;

            $getBasicCardAndAgrestiMetrics = static function (Node $node) use (&$io, &$fanOut): void {
                switch (true) {
                    case $node instanceof Stmt\Return_:
                        $io += (null !== $node->expr);
                        break;
                    case $node instanceof Node\Expr\StaticCall:
                    case $node instanceof Node\Expr\MethodCall:
                    case $node instanceof Node\Expr\NullsafeMethodCall:
                        ++$fanOut;
                }
            };
            $this->nodeIterator->iterateOver($stmt, $getBasicCardAndAgrestiMetrics);

            $this->structuralComplexityByMethod[] = $fanOut ** 2;
            $this->localComplexityByMethod[] = $io / ($fanOut + 1);
        }, $node->getMethods());

        $relativeStructuralComplexity = $this->calculateAverage($this->structuralComplexityByMethod);
        $relativeLocalComplexity = $this->calculateAverage($this->localComplexityByMethod);
        $relativeSystemComplexity = $relativeStructuralComplexity + $relativeLocalComplexity;

        $class->set('relativeStructuralComplexity', round($relativeStructuralComplexity, 2));
        $class->set('relativeDataComplexity', round($relativeLocalComplexity, 2));
        $class->set('relativeSystemComplexity', round($relativeSystemComplexity, 2));

        return null;
    }

    /**
     * Returns the arithmetical average value from the given values in argument.
     * Returns 0 if no values given.
     *
     * @param array<float> $values
     * @return float
     */
    private function calculateAverage(array $values): float
    {
        return [] === $values ? 0 : array_sum($values) / count($values);
    }
}
