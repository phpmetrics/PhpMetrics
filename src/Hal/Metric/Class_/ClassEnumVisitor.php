<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metric\Class_;

use Hal\Metric\ClassMetric;
use Hal\Metric\FunctionMetric;
use Hal\Metric\Helper\MetricClassNameGenerator;
use Hal\Metric\Helper\RoleOfMethodDetector;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\MetricsVisitorTrait;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\NodeVisitorAbstract;

/**
 * Class ClassEnumVisitor
 * Visitor that count elements that are in class-like structures.
 *
 * @package Hal\Metric\Class_
 */
class ClassEnumVisitor extends NodeVisitorAbstract
{
    use MetricsVisitorTrait;

    /**
     * Executed when leaving the traversing of the node. Used to calculates the following elements:
     * - List of methods
     * - Number of methods including the accessors
     * - Number of methods excluding the accessors
     * - Number of private/protected methods
     * - Number of public methods
     * - Number of getters
     * - Number of setters
     *
     * It also register the element in the metrics system. This visitor must be called before any visitor used for
     * metrics.
     *
     * @param Node $node The current node to leave to make the analysis.
     * @return void
     */
    public function leaveNode(Node $node)
    {
        // This visitor analysis is based on elements that might own methods. Only ClassLike can have them.
        if (!($node instanceof ClassLike)) {
            return;
        }

        $isInterface = ($node instanceof Interface_);
        $nodeName = MetricClassNameGenerator::getName($node);
        $class = $isInterface ? new InterfaceMetric($nodeName) : new ClassMetric($nodeName);
        $class->set('interface', $isInterface);

        $methods = [];

        $methodsPublic = $methodsPrivate = $nbGetters = $nbSetters = 0;
        $roleDetector = new RoleOfMethodDetector();
        foreach ($node->stmts as $stmt) {
            if (!($stmt instanceof ClassMethod)) {
                // Ignore statements that are not methods.
                continue;
            }

            $function = new FunctionMetric($stmt->name);

            $role = $roleDetector->detects($stmt);
            $function->set('role', $role);

            $nbGetters += ('getter' === $role);
            $nbSetters += ('setter' === $role);

            if (null === $role) {
                $function->set('public', $stmt->isPublic());
                $function->set('private', !$stmt->isPublic());

                $methodsPublic += $stmt->isPublic();
                $methodsPrivate += !$stmt->isPublic();
            }

            $methods[] = $function;
        }

        $class->set('methods', $methods)
            ->set('nbMethodsIncludingGettersSetters', \count($methods))
            ->set('nbMethods', \count($methods) - ($nbGetters + $nbSetters))
            ->set('nbMethodsPrivate', $methodsPrivate)
            ->set('nbMethodsPublic', $methodsPublic)
            ->set('nbMethodsGetter', $nbGetters)
            ->set('nbMethodsSetters', $nbSetters);

        $this->metrics->attach($class);
    }
}
