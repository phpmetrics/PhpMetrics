<?php

namespace Test\Hal\Metric;

use Hal\Metric\Metric;
use Hal\Metric\PackageMetric;
use PHPUnit\Framework\TestCase;

class PackageMetricTest extends TestCase
{
    public function testItIsAMetric(): void
    {
        $this->assertInstanceOf(Metric::class, new PackageMetric('PackageName\\'));
    }

    public function testItAppendsClasses(): void
    {
        $metric = new PackageMetric('PackageName\\');

        $this->assertSame([], $metric->getClasses());

        $metric->addClass('Foo');
        $this->assertSame(['Foo'], $metric->getClasses());

        $metric->addClass('Bar');
        $this->assertSame(['Foo', 'Bar'], $metric->getClasses());
    }

    public function testItMayHasAnAbstraction(): void
    {
        $metric = new PackageMetric('PackageName\\');
        $this->assertNull($metric->getAbstraction());

        $metric->setAbstraction(0.8);
        $this->assertSame(0.8, $metric->getAbstraction());
    }

    public function testItMayHasAnInstability(): void
    {
        $metric = new PackageMetric('PackageName\\');
        $this->assertNull($metric->getInstability());

        $metric->setInstability(0.8);
        $this->assertSame(0.8, $metric->getInstability());
    }

    public function testItHasAUniqueListOfOutgoingClassDependencies(): void
    {
        $metric = new PackageMetric('PackageName\\');
        $this->assertSame([], $metric->getOutgoingClassDependencies());

        $metric->addOutgoingClassDependency('PackageA\\AnyClass', 'PackageA\\');
        $metric->addOutgoingClassDependency('PackageA\\AnyClass', 'PackageA\\');

        $this->assertSame(['PackageA\\AnyClass'], $metric->getOutgoingClassDependencies());
    }

    public function testItDoesNotAddClassesOfItselfAsOutgoingClassDependencies(): void
    {
        $metric = new PackageMetric('PackageA\\');
        $metric->addOutgoingClassDependency('PackageA\\AnyClass', 'PackageA\\');
        $this->assertSame([], $metric->getOutgoingClassDependencies());
    }

    public function testItHasAUniqueListOfOutgoingPackageDependencies(): void
    {
        $metric = new PackageMetric('PackageName\\');
        $this->assertSame([], $metric->getOutgoingPackageDependencies());

        $metric->addOutgoingClassDependency('PackageA\\AnyClass', 'PackageA\\');
        $metric->addOutgoingClassDependency('PackageA\\AnotherClass', 'PackageA\\');

        $this->assertSame(['PackageA\\'], $metric->getOutgoingPackageDependencies());
    }

    public function testItDoesNotAddItselfAsOutgoingClassDependencies(): void
    {
        $metric = new PackageMetric('PackageA\\');
        $metric->addOutgoingClassDependency('PackageA\\AnyClass', 'PackageA\\');
        $this->assertSame([], $metric->getOutgoingPackageDependencies());
    }

    public function testItHasAUniqueListOfIncomingClassDependencies(): void
    {
        $metric = new PackageMetric('PackageName\\');
        $this->assertSame([], $metric->getOutgoingClassDependencies());

        $metric->addIncomingClassDependency('PackageA\\AnyClass', 'PackageA\\');
        $metric->addIncomingClassDependency('PackageA\\AnyClass', 'PackageA\\');

        $this->assertSame(['PackageA\\AnyClass'], $metric->getIncomingClassDependencies());
    }

    public function testItDoesNotAddClassesOfItselfAsIncomingClassDependencies(): void
    {
        $metric = new PackageMetric('PackageA\\');
        $metric->addIncomingClassDependency('PackageA\\AnyClass', 'PackageA\\');
        $this->assertSame([], $metric->getIncomingClassDependencies());
    }

    public function testItHasAUniqueListOfIncomingPackageDependencies(): void
    {
        $metric = new PackageMetric('PackageName\\');
        $this->assertSame([], $metric->getIncomingPackageDependencies());

        $metric->addIncomingClassDependency('PackageA\\AnyClass', 'PackageA\\');
        $metric->addIncomingClassDependency('PackageA\\AnotherClass', 'PackageA\\');

        $this->assertSame(['PackageA\\'], $metric->getIncomingPackageDependencies());
    }

    public function testItDoesNotAddItselfAsIncomingClassDependencies(): void
    {
        $metric = new PackageMetric('PackageA\\');
        $metric->addIncomingClassDependency('PackageA\\AnyClass', 'PackageA\\');
        $this->assertSame([], $metric->getIncomingPackageDependencies());
    }

    public function testItMayHasADistanceAndANormalizedDistance(): void
    {
        $metric = new PackageMetric('PackageA\\');
        $this->assertNull($metric->getDistance());
        $this->assertNull($metric->getNormalizedDistance());

        $metric->setNormalizedDistance(1);
        $this->assertSame(1, $metric->getNormalizedDistance());
        $this->assertSame(1/sqrt(2), $metric->getDistance());
    }

    public function testItMyaHasDependentInstabilities(): void
    {
        $metric = new PackageMetric('PackageB\\');
        $this->assertSame([], $metric->getDependentInstabilities());

        $metric->setDependentInstabilities(['PackageA\\' => 0.04, 'PackageC\\' => 0.5]);
        $this->assertSame(['PackageA\\' => 0.04, 'PackageC\\' => 0.5], $metric->getDependentInstabilities());
    }
}
