<?php
declare(strict_types=1);

namespace Hal\Metric\Class_\Complexity;

use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Helper\NodeIteratorInterface;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use function round;

/**
 * Calculate Kan's defects
 *
 * This metrics is specified in Chapter 4. of the book called "Metrics and Models in Software Quality Engineering"
 * written by Stephen H. Kan (ISBN: 978-0-201-72915-3).
 * Its calculation is expressed as:
 *
 *   Defects = 0.15 + 0.23 * number of do…while() + 0.22 * number of select() + 0.07 * number of if()
 *
 * In order to orient this metric calculation to PHP language and structures:
 * - the `do…while` is including `do { ... } while(...)`, `foreach (...)` and `while (...) { ... }`
 * - the `select` is including `switch (...)` and `match (...)`
 * - the `if` is including `if (...)` and `elseif (...)`
 *
 * This value represents the rate of possible anomalies that could happen when executing the analyzed code.
 * The lower this value is, the more controlled is your code.
 */
final class KanDefectVisitor extends NodeVisitorAbstract
{
    private int $nbIf;
    private int $nbDoWhile;
    private int $nbSelect;

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
            && !$node instanceof Stmt\Interface_
            && !$node instanceof Stmt\Trait_
            //TODO: && !$node instanceof Stmt\Enum_
            //TODO: Maybe replace by ClassLike ?
        ) {
            return null;
        }

        /** @var Metric $class */
        $class = $this->metrics->get(MetricNameGenerator::getClassName($node));

        $this->nbIf = 0;
        $this->nbDoWhile = 0;
        $this->nbSelect = 0;

        $this->nodeIterator->iterateOver($node, $this->calculateKanDefects(...));

        $defect = 0.15 + 0.23 * $this->nbDoWhile + 0.22 * $this->nbSelect + 0.07 * $this->nbIf;
        $class->set('kanDefect', round($defect, 2));

        return null;
    }

    /**
     * Increase the number of if, do...while or select based on the kind of the current node.
     *
     * @param Node $node
     * @return void
     */
    private function calculateKanDefects(Node $node): void
    {
        switch (true) {
            case $node instanceof Stmt\Do_:
            case $node instanceof Stmt\Foreach_:
            case $node instanceof Stmt\While_:
                ++$this->nbDoWhile;
                break;
            case $node instanceof Stmt\If_:
                ++$this->nbIf;
                break;
            case $node instanceof Stmt\Switch_:
            case $node instanceof Node\Expr\Match_:
                ++$this->nbSelect;
                break;
        }
    }
}
