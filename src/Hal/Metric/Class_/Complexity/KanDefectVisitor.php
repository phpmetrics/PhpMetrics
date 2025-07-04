<?php

namespace Hal\Metric\Class_\Complexity;

use Hal\Component\Ast\NodeTyper;
use Hal\Component\Reflected\Method;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * Calculate Kan's defects
 *
 * defects = 0.15 + 0.23 *  number of do…while() + 0.22 *  number of select() + 0.07 * number of if()
 */
class KanDefectVisitor extends NodeVisitorAbstract
{
    /**
     * @var Metrics
     */
    private $metrics;

    /**
     * @param Metrics $metrics
     */
    public function __construct(Metrics $metrics)
    {
        $this->metrics = $metrics;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(Node $node)
    {
        if (NodeTyper::isOrganizedStructure($node)) {
            $class = $this->metrics->get(getNameOfNode($node));

            $select = $while = $if = 0;

            iterate_over_node($node, function ($node) use (&$while, &$select, &$if) {
                switch (true) {
                    case $node instanceof Stmt\Do_:
                    case $node instanceof Stmt\Foreach_:
                    case $node instanceof Stmt\While_:
                        $while++;
                        break;
                    case $node instanceof Stmt\If_:
                        $if++;
                        break;
                    case $node instanceof Stmt\Switch_:
                        $select++;
                        break;
                }
            });

            $defect = 0.15 + 0.23 *  $while + 0.22 *  $select + 0.07 * $if;
            $class->set('kanDefect', round($defect, 2));
        }
    }
}
