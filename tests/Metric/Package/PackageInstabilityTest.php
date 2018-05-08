<?php

namespace Test\Hal\Metric\Package;

use Hal\Metric\Metrics;
use Hal\Metric\Package\PackageInstability;
use Hal\Metric\PackageMetric;
use PHPUnit_Framework_TestCase;

/**
 * @group metric
 * @group package
 */
class PackageInstabilityTest extends PHPUnit_Framework_TestCase
{
    public function testItCalculatesTheInstabilityOfEachPackage()
    {
        $packageA = new PackageMetric('PackageA\\');
        $packageB = new PackageMetric('PackageB\\');
        $packageC = new PackageMetric('PackageC\\');

        $packageB->addOutgoingClassDependency('ClassA', $packageA->getName());
        $packageA->addIncomingClassDependency('ClassB', $packageB->getName());

        $packageA->addOutgoingClassDependency('ClassC', $packageC->getName());
        $packageC->addIncomingClassDependency('ClassA', $packageA->getName());

        $metrics = new Metrics();
        $metrics->attach($packageA);
        $metrics->attach($packageB);
        $metrics->attach($packageC);

        (new PackageInstability())->calculate($metrics);

        $this->assertSame(0.5, $packageA->getInstability());
        $this->assertSame(1.0, $packageB->getInstability());
        $this->assertSame(0.0, $packageC->getInstability());
    }

    public function testItStoresTheInstabilityOfTheDependentPackagesOfEachPackage()
    {
        $packageA = new PackageMetric('PackageA\\');
        $packageB = new PackageMetric('PackageB\\');
        $packageC = new PackageMetric('PackageC\\');

        $packageB->addOutgoingClassDependency('ClassA', $packageA->getName());
        $packageA->addIncomingClassDependency('ClassB', $packageB->getName());

        $packageA->addOutgoingClassDependency('ClassC', $packageC->getName());
        $packageC->addIncomingClassDependency('ClassA', $packageA->getName());

        $metrics = new Metrics();
        $metrics->attach($packageA);
        $metrics->attach($packageB);
        $metrics->attach($packageC);

        (new PackageInstability())->calculate($metrics);

        $this->assertSame([$packageC->getName() => 0.0], $packageA->getDependentInstabilities());
        $this->assertSame([$packageA->getName() => 0.5], $packageB->getDependentInstabilities());
        $this->assertSame([], $packageC->getDependentInstabilities());
    }

    public function testItDoesNotCrashIfOnePackageHasNoIncomingAndNoOutgoingDependencies()
    {
        $package = new PackageMetric('PackageA\\');

        $metrics = new Metrics();
        $metrics->attach($package);

        (new PackageInstability())->calculate($metrics);

        $this->assertNull($package->getInstability());
    }
}
