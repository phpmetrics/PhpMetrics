<?php

namespace Hal\Metric\System\Coupling;

use Hal\Component\Tree\GraphDeduplicated;
use Hal\Component\Tree\Node;
use Hal\Component\Tree\Operator\SizeOfTree;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use Hal\Metric\ProjectMetric;
use Hal\ShouldNotHappenException;

/**
 * Estimates DIT
 * @see https://www.cse.iitb.ac.in/~rkj/inheritancemetrics.pdf
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class DepthOfInheritanceTree
{

    /**
     * @param Metrics $metrics
     *
     * @return void
     */
    public function calculate(Metrics $metrics)
    {
        $projectMetric = new ProjectMetric('tree');

        // building graph with parents / childs relations only
        $graph = new GraphDeduplicated();

        foreach ($metrics->all() as $metric) {
            if (!$metric instanceof ClassMetric) {
                continue;
            }

            if (!$graph->has($metric->get('name'))) {
                $graph->insert(new Node($metric->get('name')));
            }

            $to = $graph->get($metric->get('name'));
            if ($to === null) {
                throw new ShouldNotHappenException('Graph $to is null');
            }

            foreach ($metric->get('parents') as $parent) {
                if (!$graph->has($parent)) {
                    $graph->insert(new Node($parent));
                }

                $from = $graph->get($parent);
                if ($from === null) {
                    throw new ShouldNotHappenException('Graph $from is null');
                }

                $graph->addEdge($from, $to);
            }
        }

        $size = new SizeOfTree($graph);
        $averageHeight = $size->getAverageHeightOfGraph();

        $projectMetric->set('depthOfInheritanceTree', $averageHeight);
        $metrics->attach($projectMetric);
    }
}
