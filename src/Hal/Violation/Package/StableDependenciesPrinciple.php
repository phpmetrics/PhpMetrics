<?php
declare(strict_types=1);

namespace Hal\Violation\Package;

use Hal\Metric\Metric;
use Hal\Metric\PackageMetric;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use function array_filter;
use function array_keys;
use function array_map;
use function count;
use function implode;
use function round;
use function sprintf;
use function substr;

/**
 * This class triggers a violation when a package is less stable than its dependencies, violating the
 * stable-dependencies principle.
 * @see https://en.wikipedia.org/wiki/Package_principles#Principles_of_package_coupling
 */
final class StableDependenciesPrinciple implements Violation
{
    private PackageMetric $metric;

    /** @var array<string, float> */
    private array $violatingInstabilities = [];

    public function getName(): string
    {
        return 'Stable Dependencies Principle';
    }

    public function apply(Metric $metric): void
    {
        if (! $metric instanceof PackageMetric) {
            return;
        }
        $this->metric = $metric;

        $instability = $metric->getInstability();
        $violatingInstabilities = array_filter(
            $metric->getDependentInstabilities(),
            static fn (float $otherInstability): bool => $otherInstability >= $instability
        );
        if ([] !== $violatingInstabilities) {
            $this->violatingInstabilities = $violatingInstabilities;
            /** @var ViolationsHandlerInterface $violationsHandler */
            $violationsHandler = $metric->get('violations');
            $violationsHandler->add($this);
        }
    }

    public function getLevel(): int
    {
        return Violation::WARNING;
    }

    public function getDescription(): string
    {
        $instability = $this->metric->getInstability();
        if (null === $instability) {
            return 'Packages should depend in the direction of stability.';
        }

        $count = count($this->violatingInstabilities);
        $thisInstability = round($instability, 3);
        $packages = implode(
            "\n* ",
            array_map(static function (string $name, float $instability): string {
                $name = '\\' === $name ? 'global' : substr($name, 0, -1);
                return sprintf('%s (%f0.3)', $name, round($instability, 3));
            }, array_keys($this->violatingInstabilities), $this->violatingInstabilities)
        );

        return <<<EOT
Packages should depend in the direction of stability.

This package is more stable ($thisInstability) than $count package(s) that it depends on.
The packages that are more stable are

* $packages
EOT;
    }
}
