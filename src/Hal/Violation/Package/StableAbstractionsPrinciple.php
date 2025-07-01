<?php

namespace Hal\Violation\Package;

use Hal\Metric\Metric;
use Hal\Metric\PackageMetric;
use Hal\Violation\Violation;

class StableAbstractionsPrinciple implements Violation
{
    /** @var PackageMetric|null */
    private $metric;

    public function getName()
    {
        return 'Stable Abstractions Principle';
    }

    public function apply(Metric $metric)
    {
        if (! $metric instanceof PackageMetric) {
            return;
        }
        if (abs($metric->getDistance()) > sqrt(2) / 4) {
            $this->metric = $metric;
            $metric->get('violations')->add($this);
        }
    }

    public function getLevel()
    {
        return Violation::WARNING;
    }

    public function getDescription()
    {
        $violation = $this->metric->getDistance() > 0
            ? 'instable and abstract'
            : 'stable and concrete';

        return <<<EOT
            Packages should be either abstract and stable or concrete and instable.

            This package is {$violation}.
EOT;
    }
}
