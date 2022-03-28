<?php
declare(strict_types=1);

namespace Tests\Hal\Metric;

use Hal\Metric\PackageMetric;
use PHPUnit\Framework\TestCase;
use function sqrt;

final class PackageMetricTest extends TestCase
{
    public function testBagInteractionsInPackageMetricContext(): void
    {
        $bagClass = new PackageMetric('UnitTest');

        self::assertSame('UnitTest', $bagClass->getName());
        self::assertSame('UnitTest', $bagClass->get('name'));
        self::assertTrue($bagClass->has('name'));

        self::assertNull($bagClass->get('NOT A KEY'));

        $bagClass->set('other', 'FOO');
        self::assertSame('FOO', $bagClass->get('other'));

        $all = ['name' => 'UnitTest', 'other' => 'FOO'];
        self::assertSame($all, $bagClass->all());
        self::assertSame([...$all, '_type' => $bagClass::class], $bagClass->jsonSerialize());
    }

    public function testPackageMetricSpecificBehavior(): void
    {
        $packageMetric = new PackageMetric('UnitTest');

        self::assertSame([], $packageMetric->getClasses());
        self::assertNull($packageMetric->getAbstraction());
        self::assertNull($packageMetric->getInstability());
        self::assertSame([], $packageMetric->getOutgoingClassDependencies());
        self::assertSame([], $packageMetric->getOutgoingPackageDependencies());
        self::assertSame([], $packageMetric->getIncomingClassDependencies());
        self::assertSame([], $packageMetric->getIncomingPackageDependencies());
        self::assertNull($packageMetric->getDistance());
        self::assertNull($packageMetric->getNormalizedDistance());
        self::assertSame([], $packageMetric->getDependentInstabilities());

        $packageMetric->addClass('A');
        $packageMetric->addClass('B');
        $packageMetric->addClass('C');
        self::assertSame(['A', 'B', 'C'], $packageMetric->getClasses());

        $packageMetric->setAbstraction(3.14);
        $packageMetric->setInstability(22.3);
        self::assertSame(3.14, $packageMetric->getAbstraction());
        self::assertSame(22.3, $packageMetric->getInstability());

        $packageMetric->setNormalizedDistance(-89.2);
        self::assertSame(-89.2 / sqrt(2), $packageMetric->getDistance());
        self::assertSame(-89.2, $packageMetric->getNormalizedDistance());

        $packageMetric->addOutgoingClassDependency('A', 'OtherPackage');
        $packageMetric->addOutgoingClassDependency('B', 'OtherPackage');
        // Not added because package is same as packageMetric:
        $packageMetric->addOutgoingClassDependency('Ignored', 'UnitTest');
        self::assertSame(['A', 'B'], $packageMetric->getOutgoingClassDependencies());
        self::assertSame(['OtherPackage'], $packageMetric->getOutgoingPackageDependencies());

        $packageMetric->addIncomingClassDependency('A', 'OtherPackage');
        $packageMetric->addIncomingClassDependency('B', 'OtherPackage');
        // Not added because package is same as packageMetric:
        $packageMetric->addIncomingClassDependency('Ignored', 'UnitTest');
        self::assertSame(['A', 'B'], $packageMetric->getIncomingClassDependencies());
        self::assertSame(['OtherPackage'], $packageMetric->getIncomingPackageDependencies());

        $packageMetric->setDependentInstabilities([99.76, -1.2345678, 0]);
        self::assertSame([99.76, -1.2345678, 0], $packageMetric->getDependentInstabilities());
    }
}
