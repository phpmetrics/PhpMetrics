<?php

namespace Hal\Search;

use Hal\Metric\Metrics;

class PatternSearcher
{
    public function executes(Search $search, Metrics $metrics)
    {
        $found = [];
        foreach ($metrics->all() as $metric) {
            if (!$search->matches($metric)) {
                continue;
            }

            $matched = [];
            if ($metric->has('matched-searches')) {
                $matched[] = $metric->get('matched-searches');
            }
            $matched[] = $search->getName();
            $metric->set('matched-searches', $matched);


            $found[] = $metric;
        }

        return $found;
    }
}
