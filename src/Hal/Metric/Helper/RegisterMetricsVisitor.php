<?php
declare(strict_types=1);

namespace Hal\Metric\Helper;

use Hal\Metric\ClassMetric;
use Hal\Metric\FunctionMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use function array_map;

/**
 * This visitor is registering class-like node and function node found into the metrics, so all next visitors could
 * retrieve them.
 */
final class RegisterMetricsVisitor extends NodeVisitorAbstract
{
    /**
     * @param Metrics $metrics
     */
    public function __construct(
        private readonly Metrics $metrics,
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
            && !$node instanceof Stmt\Function_
            //TODO: && !$node instanceof Stmt\Enum_ ?
            //TODO: maybe simply set !$node instanceof Stmt\ClassLike ?
        ) {
            return null;
        }

        // TODO: Add enums.
        // TODO: Manage Trait separately ?
        // Attach ClassLike to metrics.
        if ($node instanceof Stmt\Interface_) {
            $class = new InterfaceMetric(MetricNameGenerator::getClassName($node));
        } elseif ($node instanceof Stmt\Function_) {
            $class = new FunctionMetric(MetricNameGenerator::getFunctionName($node));
        } else {
            $class = new ClassMetric(MetricNameGenerator::getClassName($node));
        }
        $this->metrics->attach($class);

        // Attach ClassMethods to metrics.
        if ($node instanceof Stmt\ClassLike) {
            array_map(function (Stmt\ClassMethod $stmt): void {
                $this->metrics->attach(new FunctionMetric((string)$stmt->name));
            }, $node->getMethods());
        }

        return null;
    }
}
