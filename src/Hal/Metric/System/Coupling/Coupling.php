<?php

namespace Hal\Metric\System\Coupling;

use Hal\Component\Tree\GraphDeduplicated;
use Hal\Component\Tree\Node;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;

/**
 * Estimates coupling (based on work of Henry And Kafura)
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Coupling
{

    /**
     * @param Metrics $metrics
     */
    public function calculate(Metrics $metrics)
    {

        // build a graph of relations
        $graph = new GraphDeduplicated();

        foreach ($metrics->all() as $metric) {
            if (!$metric instanceof ClassMetric) {
                continue;
            }

            if (!$graph->has($metric->get('name'))) {
                $graph->insert(new Node($metric->get('name')));
            }
            $from = $graph->get($metric->get('name'));

            foreach ($metric->get('externals') as $external) {
                if (!$graph->has($external)) {
                    $graph->insert(new Node($external));
                }

                $to = $graph->get($external);

                $graph->addEdge($from, $to);
            }
        }

        // analyze relations
        foreach ($metrics->all() as $metric) {
            if (!$metric instanceof ClassMetric) {
                continue;
            }
            $efferent = $afferent = 0;

            $node = $graph->get($metric->get('name'));
            foreach ($node->getEdges() as $edge) {

                if ($edge->getTo()->getKey() == $node->getKey()) {
                    // affects
                    $afferent++;
                }

                if ($edge->getFrom()->getKey() == $node->getKey()) {
                    // receive effects
                    $efferent++;
                }
            }

            $instability = 0;
            if ($efferent + $afferent > 0) {
                $instability = $efferent / ($afferent + $efferent);
            }
            $metric
                ->set('afferentCoupling', $afferent)
                ->set('efferentCoupling', $efferent)
                ->set('instability', round($instability, 2));
        }
    }
}
