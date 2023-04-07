<?php
declare(strict_types=1);

namespace Hal\Metric\Class_;

use Hal\Metric\FunctionMetric;
use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use function array_map;

/**
 * This visitor is parsing a class-like node to register some information.
 * Each class-like are spotted as either being an interface, an abstract class or a final class.
 * TODO: manage traits and enums.
 * Moreover, for each of them, each method is analyzed to detect if it's a getter, a setter, a public or a private
 * method. Those are also counted as "class-like-metrics".
 *
 * Metrics registered for each class-like are:
 * - is this an interface?
 * - is this an abstraction?
 * - is this a final class?
 * - list of methods metrics
 * - number of methods including accessors
 * - number of methods excluding accessors
 * - number of private or protected methods
 * - number of public methods
 */
final class ClassEnumVisitor extends NodeVisitorAbstract
{
    /**
     * @param Metrics $metrics
     */
    public function __construct(
        private readonly Metrics $metrics
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
            //TODO: && !$node instanceof Stmt\Enum_ ?
            //TODO: maybe simply set !$node instanceof Stmt\ClassLike ?
        ) {
            return null;
        }

        /** @var Metric $class */
        $class = $this->metrics->get(MetricNameGenerator::getClassName($node));

        // TODO: Add enums.
        // TODO: Manage Trait separately.
        if ($node instanceof Stmt\Interface_) {
            $class->set('interface', true);
            $class->set('abstract', true);
        } else {
            $class->set('interface', false);
            $class->set('abstract', $node instanceof Stmt\Trait_ || $node->isAbstract());
            $class->set('final', $node instanceof Stmt\Class_ && $node->isFinal());
        }

        $dataMethods = (object)[
            'nbPublic' => 0,
            'nbPrivate' => 0,
        ];
        $functionMetrics = array_map(function (Stmt\ClassMethod $stmt) use ($dataMethods): FunctionMetric {
            /** @var FunctionMetric $function */
            $function = $this->metrics->get((string)$stmt->name);
            $isPublic = $stmt->isPublic();

            $dataMethods->nbPublic += $isPublic;
            $dataMethods->nbPrivate += !$isPublic;
            $function->set('public', $isPublic);
            $function->set('private', !$isPublic);

            return $function;
        }, $node->getMethods());

        $nbMethods = count($functionMetrics);
        $class->set('methods', $functionMetrics);
        $class->set('nbMethods', $nbMethods);
        $class->set('nbMethodsPrivate', $dataMethods->nbPublic);
        $class->set('nbMethodsPublic', $dataMethods->nbPrivate);

        return null;
    }
}
