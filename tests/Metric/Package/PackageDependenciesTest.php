<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Package;

use Hal\Metric\ClassMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metrics;
use Hal\Metric\Package\PackageDependencies;
use Hal\Metric\PackageMetric;
use Phake;
use PHPUnit\Framework\TestCase;
use function array_map;

final class PackageDependenciesTest extends TestCase
{
    public function testPackageDependenciesIsCalculableWithoutClassesOrInterfaces(): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        Phake::when($metricsMock)->__call('getClassMetrics', [])->thenReturn([]);
        Phake::when($metricsMock)->__call('getInterfaceMetrics', [])->thenReturn([]);
        (new PackageDependencies($metricsMock))->calculate();

        Phake::verify($metricsMock)->__call('getClassMetrics', []);
        Phake::verify($metricsMock)->__call('getInterfaceMetrics', []);
        Phake::verifyNoOtherInteractions($metricsMock);
    }

    public function testPackageDependenciesIsCalculable(): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        $classMetrics = [Phake::mock(ClassMetric::class), Phake::mock(ClassMetric::class)];
        $interfaceMetrics = [Phake::mock(InterfaceMetric::class), Phake::mock(InterfaceMetric::class)];
        $packageMetrics = [Phake::mock(PackageMetric::class), Phake::mock(PackageMetric::class)];

        Phake::when($metricsMock)->__call('getClassMetrics', [])->thenReturn($classMetrics);
        Phake::when($metricsMock)->__call('getInterfaceMetrics', [])->thenReturn($interfaceMetrics);
        Phake::when($classMetrics[0])->__call('get', ['package'])->thenReturn('UnitTestNamespace\\A\\');
        Phake::when($classMetrics[1])->__call('get', ['package'])->thenReturn('UnitTestNamespace\\B\\');
        Phake::when($interfaceMetrics[0])->__call('get', ['package'])->thenReturn('UnitTestNamespace\\A\\');
        Phake::when($interfaceMetrics[1])->__call('get', ['package'])->thenReturn('UnitTestNamespace\\B\\');
        Phake::when($classMetrics[0])->__call('getName', [])->thenReturn('UnitTestNamespace\\A\\Foo');
        Phake::when($classMetrics[1])->__call('getName', [])->thenReturn('UnitTestNamespace\\B\\Foo');
        Phake::when($interfaceMetrics[0])->__call('getName', [])->thenReturn('UnitTestNamespace\\A\\FooInterface');
        Phake::when($interfaceMetrics[1])->__call('getName', [])->thenReturn('UnitTestNamespace\\B\\FooInterface');
        Phake::when($packageMetrics[0])->__call('getClasses', [])->thenReturn([
            'UnitTestNamespace\\A\\Foo',
            'UnitTestNamespace\\A\\FooInterface',
            'UnitTestNamespace\\A\\Bar',
            'UnitTestNamespace\\A\\AbstractBaz',
        ]);
        Phake::when($packageMetrics[1])->__call('getClasses', [])->thenReturn([
            'UnitTestNamespace\\B\\Foo',
            'UnitTestNamespace\\B\\FooInterface',
            'UnitTestNamespace\\B\\Bar',
            'UnitTestNamespace\\B\\AbstractBaz',
        ]);
        Phake::when($packageMetrics[0])->__call('getName', [])->thenReturn('UnitTestNamespace\\A\\');
        Phake::when($packageMetrics[1])->__call('getName', [])->thenReturn('UnitTestNamespace\\B\\');
        Phake::when($metricsMock)->__call('get', ['UnitTestNamespace\\A\\'])->thenReturn($packageMetrics[0]);
        Phake::when($metricsMock)->__call('get', ['UnitTestNamespace\\B\\'])->thenReturn($packageMetrics[1]);
        Phake::when($metricsMock)->__call('get', ['UnitTestNamespace\\C\\'])->thenReturn(null);
        Phake::when($metricsMock)->__call('get', ['\\'])->thenReturn(null);
        Phake::when($classMetrics[0])->__call('get', ['externals'])->thenReturn([]);
        Phake::when($classMetrics[1])->__call('get', ['externals'])->thenReturn([
            'UnitTestNamespace\\B\\FooInterface',
            'UnitTestNamespace\\A\\Foo',
            'UnitTestNamespace\\C\\NoPackage',
            'stdClass',
        ]);
        Phake::when($interfaceMetrics[0])->__call('get', ['externals'])->thenReturn([
            'UnitTestNamespace\\A\\FooInterface',
            'UnitTestNamespace\\B\\Foo',
            '__NULL__',
        ]);
        Phake::when($interfaceMetrics[1])->__call('get', ['externals'])->thenReturn([]);
        $fakeClassMetric = Phake::mock(ClassMetric::class);
        Phake::when($fakeClassMetric)->__call('get', ['package'])->thenReturn(null);
        $stdClassClassMetric = Phake::mock(ClassMetric::class);
        Phake::when($stdClassClassMetric)->__call('get', ['package'])->thenReturn(null);
        Phake::when($metricsMock)->__call('get', ['UnitTestNamespace\\A\\Foo'])->thenReturn($classMetrics[0]);
        Phake::when($metricsMock)->__call('get', ['UnitTestNamespace\\B\\Foo'])->thenReturn($classMetrics[1]);
        Phake::when($metricsMock)->__call('get', ['UnitTestNamespace\\C\\NoPackage'])->thenReturn($fakeClassMetric);
        Phake::when($metricsMock)->__call('get', ['stdClass'])->thenReturn($stdClassClassMetric);
        Phake::when($metricsMock)->__call('get', ['__NULL__'])->thenReturn(null);
        Phake::when($packageMetrics[0])
            ->__call('addOutgoingClassDependency', ['UnitTestNamespace\\B\\Foo', 'UnitTestNamespace\\B\\'])
            ->thenDoNothing();
        Phake::when($packageMetrics[0])->__call('addOutgoingClassDependency', ['__NULL__', '\\'])->thenDoNothing();
        Phake::when($packageMetrics[1])
            ->__call('addOutgoingClassDependency', ['UnitTestNamespace\\A\\Foo', 'UnitTestNamespace\\A\\'])
            ->thenDoNothing();
        Phake::when($packageMetrics[1])
            ->__call('addOutgoingClassDependency', ['UnitTestNamespace\\C\\NoPackage', 'UnitTestNamespace\\C\\'])
            ->thenDoNothing();
        Phake::when($packageMetrics[1])->__call('addOutgoingClassDependency', ['stdClass', '\\'])->thenDoNothing();
        Phake::when($packageMetrics[0])
            ->__call('addIncomingClassDependency', ['UnitTestNamespace\\B\\Foo', 'UnitTestNamespace\\B\\'])
            ->thenDoNothing();
        Phake::when($packageMetrics[1])
            ->__call('addIncomingClassDependency', ['UnitTestNamespace\\A\\Foo', 'UnitTestNamespace\\A\\'])
            ->thenDoNothing();

        (new PackageDependencies($metricsMock))->calculate();

        Phake::verify($metricsMock)->__call('getClassMetrics', []);
        Phake::verify($metricsMock)->__call('getInterfaceMetrics', []);
        Phake::verify($metricsMock, Phake::times(3))->__call('get', ['UnitTestNamespace\\A\\']);
        Phake::verify($metricsMock, Phake::times(3))->__call('get', ['UnitTestNamespace\\B\\']);
        Phake::verify($metricsMock)->__call('get', ['UnitTestNamespace\\C\\']);
        Phake::verify($metricsMock, Phake::times(2))->__call('get', ['\\']);
        Phake::verify($metricsMock)->__call('get', ['UnitTestNamespace\\A\\Foo']);
        Phake::verify($metricsMock)->__call('get', ['UnitTestNamespace\\B\\Foo']);
        Phake::verify($metricsMock)->__call('get', ['UnitTestNamespace\\C\\NoPackage']);
        Phake::verify($metricsMock)->__call('get', ['stdClass']);
        Phake::verify($metricsMock)->__call('get', ['__NULL__']);
        Phake::verify($classMetrics[0], Phake::times(2))->__call('get', ['package']);
        Phake::verify($classMetrics[0])->__call('get', ['externals']);
        Phake::verify($classMetrics[0], Phake::never())->__call('getName', []); // No externals
        Phake::verify($classMetrics[1], Phake::times(2))->__call('get', ['package']);
        Phake::verify($classMetrics[1])->__call('get', ['externals']);
        Phake::verify($classMetrics[1])->__call('getName', []);
        Phake::verify($interfaceMetrics[0])->__call('get', ['package']);
        Phake::verify($interfaceMetrics[0])->__call('get', ['externals']);
        Phake::verify($interfaceMetrics[0])->__call('getName', []);
        Phake::verify($interfaceMetrics[1])->__call('get', ['package']);
        Phake::verify($interfaceMetrics[1])->__call('get', ['externals']);
        Phake::verify($interfaceMetrics[1], Phake::never())->__call('getName', []);  // No externals
        Phake::verify($packageMetrics[0], Phake::times(3))->__call('getClasses', []);
        Phake::verify($packageMetrics[0])->__call('getName', []);
        Phake::verify($packageMetrics[0])->__call(
            'addOutgoingClassDependency',
            ['UnitTestNamespace\\B\\Foo', 'UnitTestNamespace\\B\\']
        );
        Phake::verify($packageMetrics[0])->__call('addOutgoingClassDependency', ['__NULL__', '\\']);
        Phake::verify($packageMetrics[0])->__call(
            'addIncomingClassDependency',
            ['UnitTestNamespace\\B\\Foo', 'UnitTestNamespace\\B\\']
        );
        Phake::verify($packageMetrics[1], Phake::times(4))->__call('getClasses', []);
        Phake::verify($packageMetrics[1])->__call('getName', []);
        Phake::verify($packageMetrics[1])->__call(
            'addOutgoingClassDependency',
            ['UnitTestNamespace\\A\\Foo', 'UnitTestNamespace\\A\\']
        );
        Phake::verify($packageMetrics[1])->__call(
            'addOutgoingClassDependency',
            ['UnitTestNamespace\\C\\NoPackage', 'UnitTestNamespace\\C\\']
        );
        Phake::verify($packageMetrics[1])->__call(
            'addOutgoingClassDependency',
            ['stdClass', '\\']
        );
        Phake::verify($packageMetrics[1])->__call(
            'addIncomingClassDependency',
            ['UnitTestNamespace\\A\\FooInterface', 'UnitTestNamespace\\A\\']
        );

        array_map(
            Phake::verifyNoOtherInteractions(...),
            [$metricsMock, ...$classMetrics, ...$interfaceMetrics, ...$packageMetrics]
        );
    }
}
