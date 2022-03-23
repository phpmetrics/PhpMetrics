<?php

namespace Hal\Violation;

use Hal\Metric\Metrics;
use Hal\Violation\Class_;
use Hal\Violation\Package;
use Hal\Violation\Search\SearchShouldNotBeFoundPrinciple;

class ViolationParser
{

    /**
     * @param Metrics $metrics
     * @return $this
     */
    public function apply(Metrics $metrics)
    {
        $violations = [
            new Class_\Blob(),
            new Class_\TooComplexClassCode(),
            new Class_\TooComplexMethodCode(),
            new Class_\ProbablyBugged(),
            new Class_\TooLong(),
            new Class_\TooDependent(),
            new Package\StableAbstractionsPrinciple(),
            new Package\StableDependenciesPrinciple(),
            new SearchShouldNotBeFoundPrinciple(),
        ];

        foreach ($metrics->all() as $metric) {
            $metric->set('violations', new Violations);

            foreach ($violations as $violation) {
                $violation->apply($metric);
            }
        }

        return $this;
    }
}
