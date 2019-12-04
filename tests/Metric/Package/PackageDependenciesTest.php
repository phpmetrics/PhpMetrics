<?php

namespace Test\Hal\Metric\Package;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use Hal\Metric\Package\PackageDependencies;
use Hal\Metric\PackageMetric;
use \PHPUnit\Framework\TestCase;

/**
 * @group metric
 * @group package
 */
class PackageDependenciesTest extends TestCase
{
    public function testItCollectsAllIncomingAndOutgoingPackageDependencies()
    {
        $packageA = new PackageMetric('PackageA\\');
        $packageB = new PackageMetric('PackageB\\');

        $classWithExternalsOfB = new ClassMetric('PackageA\\AnotherClass');
        $classWithExternalsOfB->set('package', 'PackageA\\');
        $classWithExternalsOfB->set('externals', ['ClassB', 'PackageB\\ClassB']);

        $classWithGlobalExternal = new ClassMetric('ClassA');
        $classWithGlobalExternal->set('package', 'PackageA\\');
        $classWithGlobalExternal->set('externals', ['stdClass']);

        $metrics = new Metrics();
        $metrics->attach($packageA);
        $metrics->attach($packageB);

        $metrics->attach($classWithGlobalExternal);
        $metrics->attach($classWithExternalsOfB);

        $metrics->attach((new ClassMetric('ClassB'))->set('package', 'PackageB\\'));
        $metrics->attach(new ClassMetric('PackageB\\ClassB'));

        (new PackageDependencies())->calculate($metrics);

        $this->assertSame(['stdClass', 'ClassB', 'PackageB\\ClassB'], $packageA->getOutgoingClassDependencies());
        $this->assertSame(['\\', 'PackageB\\'], $packageA->getOutgoingPackageDependencies());
        $this->assertSame(['PackageA\\AnotherClass'], $packageB->getIncomingClassDependencies());
        $this->assertSame(['PackageA\\'], $packageB->getIncomingPackageDependencies());
    }

    public function testItSkipsClassesThatHasNoDependencies()
    {
        $metrics = new Metrics();
        $metrics->attach((new ClassMetric('OneClass'))->set('package', 'PackageA\\'));

        (new PackageDependencies())->calculate($metrics);
    }

    public function testItSkipsClassesThatHasNoPackage()
    {
        $metrics = new Metrics();
        $metrics->attach((new ClassMetric('OneClass'))->set('externals', ['AnotherClass']));

        (new PackageDependencies())->calculate($metrics);
    }
}
