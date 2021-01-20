<?php
namespace Hal\Metric\Class_;

use Hal\Metric\ClassMetric;
use Hal\Metric\FunctionMetric;
use Hal\Metric\Helper\RoleOfMethodDetector;
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
            || $node instanceof Stmt\Trait_
        ) {
            if ($node instanceof Stmt\Interface_) {
                $class = new InterfaceMetric($node->namespacedName->toString());
                $class->set('interface', true);
                $class->set('abstract', true);
            } else {
                $name = (string)(isset($node->namespacedName) ? $node->namespacedName : 'anonymous@' . spl_object_hash($node));
                $class = new ClassMetric($name);
                $class->set('interface', false);
                $class->set('abstract', $node instanceof Stmt\Trait_ || $node->isAbstract());
                $class->set('final', !$node instanceof Stmt\Trait_ && $node->isFinal());
            }

            $methods = [];

            $methodsPublic = $methodsPrivate = $nbGetters = $nbSetters = 0;
            $roleDetector = new RoleOfMethodDetector();
            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Stmt\ClassMethod) {
                    $function = new FunctionMetric((string)$stmt->name);

                    $role = $roleDetector->detects($stmt);
                    $function->set('role', $role);
                    switch ($role) {
                        case 'getter':
                            $nbGetters++;
                            break;
                        case 'setter':
                            $nbSetters++;
                            break;
                    }

                    if (null === $role) {
                        if ($stmt->isPublic()) {
                            $methodsPublic++;
                            $function->set('public', true);
                            $function->set('private', false);
                        }

                        if ($stmt->isPrivate() || $stmt->isProtected()) {
                            $methodsPrivate++;
                            $function->set('public', false);
                            $function->set('private', true);
                        }
                    }

                    array_push($methods, $function);
                }
            }

            $class->set('methods', $methods);
            $class->set('nbMethodsIncludingGettersSetters', count($methods));
            $class->set('nbMethods', count($methods) - ($nbGetters + $nbSetters));
            $class->set('nbMethodsPrivate', $methodsPrivate);
            $class->set('nbMethodsPublic', $methodsPublic);
            $class->set('nbMethodsGetter', $nbGetters);
            $class->set('nbMethodsSetters', $nbSetters);

            $this->metrics->attach($class);
        }
    }
}
