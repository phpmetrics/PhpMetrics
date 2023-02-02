<?php
declare(strict_types=1);

namespace Hal\Metric\System\Coupling;

use Hal\Component\Tree\GraphDeduplicated;
use Hal\Component\Tree\Node;
use Hal\Metric\CalculableInterface;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use function array_map;
use function round;

/**
 * Estimates coupling (based on work of Henry and Kafura).
 * This calculable class is using dependencies registered for each class to calculate:
 * - the efferent coupling: number of classes that uses the given class
 * - the afferent coupling: number of classes used by the given class
 * - the instability: ratio of efferent classes to all dependencies.
 */
final class Coupling implements CalculableInterface
{
    public function __construct(private readonly Metrics $metrics)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(): void
    {
        $graph = $this->buildRelationGraph();

        // Analyze relations
        foreach ($this->metrics->getClassMetrics() as $metric) {
            $efferent = $afferent = 0;
            /** @var string $name */
            $name = $metric->get('name');

            /** @var Node $node */
            $node = $graph->get($name);
            foreach ($node->getEdges() as $edge) {
                $afferent += ($edge->getTo()->getKey() === $node->getKey());
                $efferent += ($edge->getFrom()->getKey() === $node->getKey());
            }

            $instability = ($efferent + $afferent > 0) ? $efferent / ($afferent + $efferent) : 0;
            $metric->set('afferentCoupling', $afferent);
            $metric->set('efferentCoupling', $efferent);
            $metric->set('instability', round($instability, 2));
        }
    }

    /**
     * Builds a graph of relations between classes and their dependencies.
     *
     * @return GraphDeduplicated
     */
    private function buildRelationGraph(): GraphDeduplicated
    {
        $graph = new GraphDeduplicated();

        array_map(static function (ClassMetric $metric) use ($graph): void {
            /** @var string $name */
            $name = $metric->get('name');
            $from = $graph->gather($name);
            /** @var array<string> $externals */
            $externals = $metric->get('externals');
            array_map(static function (string $external) use ($graph, $from): void {
                $graph->addEdge($from, $graph->gather($external));
            }, $externals);
        }, $this->metrics->getClassMetrics());

        return $graph;
    }
}
