<?php
namespace Hal\Metric\Class_\Coupling;

use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * List externals dependencies
 *
 * Class ExternalsVisitor
 * @package Hal\Metric\Class_\Coupling
 */
class ExternalsVisitor extends NodeVisitorAbstract
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

    /**
     * @inheritdoc
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Stmt\Class_
            || $node instanceof Stmt\Interface_
        ) {

            $class = $this->metrics->get($node->namespacedName->toString());

            $dependencies = [];

            // extends
            if (isset($node->extends)) {
                if (is_array($node->extends)) {
                    foreach ((array)$node->extends as $interface) {
                        array_push($dependencies, (string)$interface);
                    }
                } else {
                    array_push($dependencies, (string)$node->extends);
                }
            }

            // implements
            if (isset($node->implements)) {
                foreach ($node->implements as $interface) {
                    array_push($dependencies, (string)$interface);
                }
            }

            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Stmt\ClassMethod) {


                    // return
                    if (isset($stmt->returnType)) {
                        if ($stmt->returnType instanceof Node\Name\FullyQualified) {
                            array_push($dependencies, (string)$stmt->returnType);
                        }
                    }

                    // Type hint of method's parameters
                    foreach ($stmt->params as $param) {
                        if ($param->type) {
                            if ($param->type instanceof Node\Name\FullyQualified) {
                                array_push($dependencies, (string)$param->type);
                            }
                        }
                    }

                    \iterate_over_node($stmt, function ($node) use (&$dependencies) {
                        switch (true) {
                            case $node instanceof Node\Expr\New_:
                                // new MyClass
                                array_push($dependencies, getNameOfNode($node));
                                break;
                            case $node instanceof Node\Expr\StaticCall:
                                // MyClass::Call
                                array_push($dependencies, getNameOfNode($node));
                                break;
                        }
                    });

                }
            }

            $class->set('externals', $dependencies);
        }
    }
}
