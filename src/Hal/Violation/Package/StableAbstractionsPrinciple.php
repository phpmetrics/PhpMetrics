<?php
declare(strict_types=1);

namespace Hal\Violation\Package;

use Hal\Metric\Metric;
use Hal\Metric\PackageMetric;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use function abs;
use function sqrt;

/**
 * This class triggers a violation when a package is either unstable and abstract or stable and concrete at the same
 * time, violating the stable abstraction principle.
 * @see https://en.wikipedia.org/wiki/Package_principles#Principles_of_package_coupling
 */
final class StableAbstractionsPrinciple implements Violation
{
    private PackageMetric $metric;

    public function getName(): string
    {
        return 'Stable Abstractions Principle';
    }

    public function apply(Metric $metric): void
    {
        if (! $metric instanceof PackageMetric) {
            return;
        }

        $this->metric = $metric;

        $distance = $metric->getDistance();
        if (null === $distance) {
            return;
        }

        if (abs($distance) > sqrt(2) / 4) {
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
        $violation = $this->metric->getDistance() > 0 ? 'unstable and abstract' : 'stable and concrete';

        return <<<EOT
Packages should be either abstract and stable or concrete and unstable.

This package is $violation.
EOT;
    }
}
