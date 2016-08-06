<?php

namespace Hal\Metric\System\Coupling;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;

/**
 * Class PageRank
 * @package Hal\Metric\System\Coupling
 */
class PageRank
{

    /**
     * @param Metrics $metrics
     */
    public function calculate(Metrics $metrics)
    {

        // build an array of relations
        $links = [];
        foreach ($metrics->all() as $metric) {
            if (!$metric instanceof ClassMetric) {
                continue;
            }

            $links[$metric->get('name')] = $metric->get('externals');
        }

        $ranks = $this->calculatePageRank($links);

        // save in the metrics object
        foreach ($ranks as $name => $rank) {
            $metrics->get($name)->set('pageRank', round($rank, 2));
        }
    }

    /**
     * @see http://phpir.com/pagerank-in-php/
     * @param $linkGraph
     * @param float $dampingFactor
     * @return array
     */
    private function calculatePageRank($linkGraph, $dampingFactor = 0.15)
    {
        $pageRank = array();
        $tempRank = array();
        $nodeCount = count($linkGraph);

        // initialise the PR as 1/n
        foreach ($linkGraph as $node => $outbound) {
            $pageRank[$node] = 1 / $nodeCount;
            $tempRank[$node] = 0;
        }

        $change = 1;
        $i = 0;
        while ($change > 0.00005 && $i < 100) {
            $change = 0;
            $i++;

            // distribute the PR of each page
            foreach ($linkGraph as $node => $outbound) {
                $outboundCount = count($outbound);
                foreach ($outbound as $link) {
                    // case of unversionned dependency
                    if (!isset($tempRank[$link])) {
                        $tempRank[$link] = 0;
                    }
                    $tempRank[$link] += $pageRank[$node] / $outboundCount;
                }
            }

            $total = 0;
            // calculate the new PR using the damping factor
            foreach ($linkGraph as $node => $outbound) {
                $tempRank[$node] = ($dampingFactor / $nodeCount)
                    + (1 - $dampingFactor) * $tempRank[$node];
                $change += abs($pageRank[$node] - $tempRank[$node]);
                $pageRank[$node] = $tempRank[$node];
                $tempRank[$node] = 0;
                $total += $pageRank[$node];
            }

            // Normalise the page ranks so it's all a proportion 0-1
            foreach ($pageRank as $node => $score) {
                $pageRank[$node] /= $total;
            }
        }

        return $pageRank;
    }

}