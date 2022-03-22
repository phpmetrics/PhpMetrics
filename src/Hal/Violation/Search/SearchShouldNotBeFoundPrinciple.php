<?php

namespace Hal\Violation\Search;

use Hal\Metric\Metric;
use Hal\Violation\Violation;

class SearchShouldNotBeFoundPrinciple implements Violation
{

    private $concernedSearches = [];

    public function getName()
    {
        return implode(', ', $this->concernedSearches);
    }

    public function apply(Metric $metric)
    {
        if ($metric->has('was-not-expected') && $metric->get('was-not-expected')) {
            $this->concernedSearches = array_unique(
                array_merge(
                    $this->concernedSearches,
                    $metric->get('was-not-expected-by')
                )
            );
            $metric->get('violations')->add($this);
        }
    }

    public function getLevel()
    {
        return Violation::CRITICAL;
    }

    public function getDescription()
    {
        return 'According configuration, this component is not expected to be found in the code.';
    }
}
