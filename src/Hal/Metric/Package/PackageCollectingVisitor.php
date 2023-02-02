<?php
declare(strict_types=1);

namespace Hal\Metric\Package;

use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use function preg_match;

/**
 * Prepares packages metrics for each package.
 * By default, packages are grouped by namespace, but it can be overloaded by the `package` or `subpackage` annotation.
 * This visitor is only registering package metrics without calculating any metric.
 */
final class PackageCollectingVisitor extends NodeVisitorAbstract
{
    private string $namespace = '';

    public function __construct(
        private readonly Metrics $metrics
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function enterNode(Node $node): null|int|Node // TODO PHP 8.2: only return null here.
    {
        if ($node instanceof Stmt\Namespace_) {
            $this->namespace = (string)$node->name;
        }

        return null;
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
            // TODO : replace by ClassLike ?
        ) {
            return null;
        }

        $package = $this->namespace;

        $docBlockText = (string)$node->getDocComment()?->getText();
        if (1 === preg_match('/^\s*\*\s*@package\s+(.*)/m', $docBlockText, $matches)) {
            $package = $matches[1];
        }
        if (1 === preg_match('/^\s*\*\s*@subpackage\s+(.*)/m', $docBlockText, $matches)) {
            $package .= '\\' . $matches[1];
        }

        $packageName = $package . '\\';
        if (!$this->metrics->has($packageName)) {
            $this->metrics->attach(new PackageMetric($packageName));
        }
        /** @var PackageMetric $packageMetric */
        $packageMetric = $this->metrics->get($packageName);

        $elementName = MetricNameGenerator::getClassName($node);

        $packageMetric->addClass($elementName);

        /** @var Metric $class */
        $class = $this->metrics->get($elementName);
        $class->set('package', $packageName);

        return null;
    }
}
