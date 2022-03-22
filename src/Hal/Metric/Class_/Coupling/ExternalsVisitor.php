<?php
namespace Hal\Metric\Class_\Coupling;

use Hal\Metric\Helper\MetricClassNameGenerator;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * List externals dependencies
 *
 * @package Hal\Metric\Class_\Coupling
 */
class ExternalsVisitor extends NodeVisitorAbstract
{

    /**
     * @var Metrics
     */
    private $metrics;

    /**
     * @var Stmt\UseUse[]
     */
    private $uses = [];

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
        if ($node instanceof Stmt\Namespace_) {
            $this->uses = [];
        }

        if ($node instanceof Stmt\Use_) {
            $this->uses = array_merge($this->uses, $node->uses);
        }

        if ($node instanceof Stmt\Class_
            || $node instanceof Stmt\Interface_
            || $node instanceof Stmt\Trait_
        ) {
            $class = $this->metrics->get(MetricClassNameGenerator::getName($node));
            $parents = [];
            $interfaces = [];

            $dependencies = [];

            // extends
            if (isset($node->extends)) {
                if (is_array($node->extends)) {
                    foreach ((array)$node->extends as $interface) {
                        $this->pushToDependencies($dependencies, (string)$interface);
                        array_push($parents, (string)$interface);
                    }
                } else {
                    $this->pushToDependencies($dependencies, (string)$node->extends);
                    array_push($parents, (string)$node->extends);
                }
            }

            // implements
            if (isset($node->implements)) {
                foreach ($node->implements as $interface) {
                    $this->pushToDependencies($dependencies, (string)$interface);
                    array_push($interfaces, (string)$interface);
                }
            }

            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Stmt\ClassMethod) {
                    // return
                    if (isset($stmt->returnType)) {
                        if ($stmt->returnType instanceof Node\Name\FullyQualified) {
                            $this->pushToDependencies($dependencies, (string)$stmt->returnType);
                        }
                    }

                    // Type hint of method's parameters
                    foreach ($stmt->params as $param) {
                        if ($param->type) {
                            if ($param->type instanceof Node\Name\FullyQualified) {
                                $this->pushToDependencies($dependencies, (string)$param->type);
                            }
                        }
                    }

                    // instantiations, static calls
                    \iterate_over_node($stmt, function ($node) use (&$dependencies) {
                        switch (true) {
                            case $node instanceof Node\Expr\New_:
                                // new MyClass
                                $this->pushToDependencies($dependencies, getNameOfNode($node));
                                break;
                            case $node instanceof Node\Expr\StaticCall:
                                // MyClass::Call
                                $this->pushToDependencies($dependencies, getNameOfNode($node));
                                break;
                        }
                    });

                    // annotations
                    $comments = $stmt->getDocComment();
                    if ($comments && false !== preg_match_all('!\s+\*\s+@(\w+)!', $comments->getText(), $matches)) {
                        foreach ($matches[1] as $check) {
                            foreach ($this->uses as $use) {
                                if (method_exists($use, 'getAlias')) {
                                    if (((string)$use->getAlias()) === $check) {
                                        $this->pushToDependencies($dependencies, (string)($use->name));
                                    }
                                    continue;
                                }
                                if ($use->alias === $check) {
                                    $this->pushToDependencies($dependencies, (string)($use->name));
                                }
                            }
                        }
                    }
                }
            }

            $class->set('externals', $dependencies);
            $class->set('parents', $parents);
            $class->set('implements', $interfaces);
        }
    }

    private function pushToDependencies(array &$dependencies, $dependency)
    {
        $lowercase = strtolower($dependency);
        if ('self' === $lowercase || 'parent' === $lowercase) {
            return;
        }
        array_push($dependencies, (string)$dependency);
    }
}
