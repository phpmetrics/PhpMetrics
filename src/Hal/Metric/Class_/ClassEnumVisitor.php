<?php
namespace Hal\Metric\Class_;

use Hal\Component\Reflected\Method;
use Hal\Metric\ClassMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

class ClassEnumVisitor extends NodeVisitorAbstract
{

    /**
     * @var Metrics
     */
    private $metrics;

    /**
     * ClassEnumVisitor constructor.
     * @param Metrics $metrics
     */
    public function __construct(Metrics $metrics)
    {
        $this->metrics = $metrics;
    }


    public function leaveNode(Node $node)
    {
        if ($node instanceof Stmt\Class_
            || $node instanceof Stmt\Interface_
        ) {

            if ($node instanceof Stmt\Interface_) {
                $class = new InterfaceMetric($node->namespacedName->toString());
                $class->set('interface', true);
            } else {
                $class = new ClassMetric($node->namespacedName->toString());
                $class->set('interface', false);
            }
            $class->set('name', $node->namespacedName->toString());

            $methods = 0;
            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Stmt\ClassMethod) {
                    $methods++;
                }
            }
            $class->set('nbMethods', $methods);

            $this->metrics->attach($class);
        }
    }
}