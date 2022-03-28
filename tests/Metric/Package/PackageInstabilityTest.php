<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Package;

use Hal\Metric\Metrics;
use Hal\Metric\Package\PackageInstability;
use Hal\Metric\PackageMetric;
use Phake;
use PHPUnit\Framework\TestCase;
use function array_map;

final class PackageInstabilityTest extends TestCase
{
    public function testPackageInstabilityWithoutPackages(): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        Phake::when($metricsMock)->__call('getPackageMetrics', [])->thenReturn([]);

        (new PackageInstability($metricsMock))->calculate();

        Phake::verify($metricsMock)->__call('getPackageMetrics', []);
        Phake::verifyNoOtherInteractions($metricsMock);
    }

    public function testPackageInstabilityIsCalculable(): void
    {
        $metricsMock = Phake::mock(Metrics::class);
        $packages = [
            Phake::mock(PackageMetric::class), // Empty package
            Phake::mock(PackageMetric::class), // No package dependencies
            Phake::mock(PackageMetric::class), // Package interdependency with "empty package", no class.
            Phake::mock(PackageMetric::class), // Package interdependency with "NoPackageDependencies", with class.
        ];
        Phake::when($metricsMock)->__call('getPackageMetrics', [])->thenReturn($packages);
        Phake::when($packages[0])->__call('getName', [])->thenReturn('EmptyPackage');
        Phake::when($packages[0])->__call('getIncomingClassDependencies', [])->thenReturn([]);
        Phake::when($packages[0])->__call('getOutgoingClassDependencies', [])->thenReturn([]);
        Phake::when($packages[0])->__call('getOutgoingPackageDependencies', [])->thenReturn([]);
        Phake::when($packages[1])->__call('getName', [])->thenReturn('NoPackageDependencies');
        Phake::when($packages[1])->__call('getIncomingClassDependencies', [])->thenReturn(['A', 'B', 'C']);
        Phake::when($packages[1])->__call('getOutgoingClassDependencies', [])->thenReturn(['D', 'E']);
        Phake::when($packages[1])->__call('getOutgoingPackageDependencies', [])->thenReturn([]);
        Phake::when($packages[2])->__call('getName', [])->thenReturn('NoClassDependencies');
        Phake::when($packages[2])->__call('getIncomingClassDependencies', [])->thenReturn([]);
        Phake::when($packages[2])->__call('getOutgoingClassDependencies', [])->thenReturn([]);
        Phake::when($packages[2])->__call('getOutgoingPackageDependencies', [])->thenReturn(['EmptyPackage']);
        Phake::when($packages[3])->__call('getName', [])->thenReturn('OmegaPackage');
        Phake::when($packages[3])->__call('getIncomingClassDependencies', [])->thenReturn(['A', 'B', 'C']);
        Phake::when($packages[3])->__call('getOutgoingClassDependencies', [])->thenReturn(['D']);
        Phake::when($packages[3])->__call('getOutgoingPackageDependencies', [])->thenReturn(['NoPackageDependencies']);
        $instabilityCollector = [];
        foreach ($packages as $i => $package) {
            Phake::when($package)->__call('setInstability', [Phake::anyParameters()])->thenReturnCallback(
                static function (float $instability) use (&$instabilityCollector, $i): void {
                    $instabilityCollector[$i] = $instability;
                }
            );
            Phake::when($package)->__call('setDependentInstabilities', [Phake::anyParameters()])->thenDoNothing();
        }
        Phake::when($packages[0])->__call('getInstability', [])->thenReturn(null);
        Phake::when($packages[1])->__call('getInstability', [])->thenReturn(2 / 5);
        Phake::when($packages[2])->__call('getInstability', [])->thenReturn(null);
        Phake::when($packages[3])->__call('getInstability', [])->thenReturn(1 / 4);

        (new PackageInstability($metricsMock))->calculate();

        Phake::verify($metricsMock)->__call('getPackageMetrics', []);
        foreach ($packages as $package) {
            Phake::verify($package)->__call('getIncomingClassDependencies', []);
            Phake::verify($package)->__call('getOutgoingClassDependencies', []);
            Phake::verify($package)->__call('getOutgoingPackageDependencies', []);
        }
        Phake::verify($packages[0], Phake::never())->__call('setInstability', [Phake::anyParameters()]);
        Phake::verify($packages[0], Phake::never())->__call('getName', []);
        Phake::verify($packages[0], Phake::never())->__call('getInstability', []);
        Phake::verify($packages[0])->__call('setDependentInstabilities', [[]]);
        Phake::verify($packages[1])->__call('setInstability', [2 / 5]);
        Phake::verify($packages[1])->__call('getName', []);
        Phake::verify($packages[1])->__call('getInstability', []);
        Phake::verify($packages[1])->__call('setDependentInstabilities', [[]]);
        Phake::verify($packages[2], Phake::never())->__call('setInstability', [Phake::anyParameters()]);
        Phake::verify($packages[2], Phake::never())->__call('getName', []);
        Phake::verify($packages[2], Phake::never())->__call('getInstability', []);
        Phake::verify($packages[2])->__call('setDependentInstabilities', [[]]);
        Phake::verify($packages[3])->__call('setInstability', [1 / 4]);
        Phake::verify($packages[3])->__call('getName', []);
        Phake::verify($packages[3])->__call('getInstability', []);
        Phake::verify($packages[3])->__call('setDependentInstabilities', [['NoPackageDependencies' => 2 / 5]]);

        self::assertSame([1 => 2 / 5, 3 => 1 / 4], $instabilityCollector);

        array_map(Phake::verifyNoOtherInteractions(...), $packages);
        Phake::verifyNoOtherInteractions($metricsMock);
    }
}
