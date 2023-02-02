<?php
declare(strict_types=1);

namespace Hal\Metric\System\Coupling;

use Hal\Component\Tree\GraphDeduplicated;
use Hal\Component\Tree\Operator\SizeOfTree;
use Hal\Metric\CalculableInterface;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use Hal\Metric\ProjectMetric;
use function array_map;

/**
 * Estimates Depth of inheritance tree.
 * @see https://www.cse.iitb.ac.in/~rkj/inheritancemetrics.pdf
 */
final class DepthOfInheritanceTree implements CalculableInterface
{
    public function __construct(private readonly Metrics $metrics)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(): void
    {
        $projectMetric = new ProjectMetric('tree');
        $averageHeight = (new SizeOfTree($this->buildParentChildGraph()))->getAverageHeightOfGraph();
        $projectMetric->set('depthOfInheritanceTree', $averageHeight);
        $this->metrics->attach($projectMetric);
    }

    /**
     * Builds a graph with parent-child relations only.
     *
     * @return GraphDeduplicated
     */
    private function buildParentChildGraph(): GraphDeduplicated
    {
        $graph = new GraphDeduplicated();

        array_map(static function (ClassMetric $metric) use ($graph): void {
            /** @var string $name */
            $name = $metric->get('name');
            $to = $graph->gather($name);
            /** @var array<string> $parents */
            $parents = $metric->get('parents');
            array_map(static function (string $parent) use ($graph, $to): void {
                $graph->addEdge($graph->gather($parent), $to);
            }, $parents);
        }, $this->metrics->getClassMetrics());

        return $graph;
    }
}
