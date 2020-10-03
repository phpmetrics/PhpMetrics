<?php

namespace Hal\Violation\Package;

use Hal\Metric\Metric;
use Hal\Metric\PackageMetric;
use Hal\ShouldNotHappenException;
use Hal\Violation\Violation;

class StableDependenciesPrinciple implements Violation
{
    /** @var PackageMetric|null */
    private $metric;

    /** @var float[] */
    private $violatingInstabilities = [];

    public function getName()
    {
        return 'Stable Dependencies Principle';
    }

    public function apply(Metric $metric)
    {
        if (! $metric instanceof PackageMetric) {
            return;
        }
        $instability = $metric->getInstability();
        $violatingInstabilities = array_filter(
            $metric->getDependentInstabilities(),
            function ($otherInstability) use ($instability) {
                return $otherInstability >= $instability;
            }
        );
        if (count($violatingInstabilities) > 0) {
            $this->violatingInstabilities = $violatingInstabilities;
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
        if ($this->metric === null) {
            throw new ShouldNotHappenException('Metric property is null');
        }

        $value = $this->metric->getInstability();
        if ($value === null) {
            throw new ShouldNotHappenException('Instability is null');
        }

        $count = count($this->violatingInstabilities);
        $thisInstability = round($value, 3);
        $packages = implode("\n* ", array_map(function ($name, $instability) {
            $name = $name === '\\' ? 'global' : substr((string)$name, 0, -1);
            $instability = round($instability, 3);
            return "$name ($instability)";
        }, array_keys($this->violatingInstabilities), $this->violatingInstabilities));
        return <<<EOT
Packages should depend in the direction of stability.

This package is more stable ({$thisInstability}) than {$count} package(s) that it depends on.
The packages that are more stable are

* {$packages}
EOT;
    }
}
