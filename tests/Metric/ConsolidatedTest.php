<?php
declare(strict_types=1);

namespace Tests\Hal\Metric;

use Hal\Metric\ClassMetric;
use Hal\Metric\Consolidated;
use Hal\Metric\FileMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;
use Hal\Metric\ProjectMetric;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use Phake;
use PHPUnit\Framework\TestCase;

final class ConsolidatedTest extends TestCase
{
    public function testConsolidationWithAllKindOfMetrics(): void
    {
        $metrics = new Metrics();
        $metrics->attach(new ClassMetric('Class_A'));
        $metrics->attach(new ClassMetric('Class_B'));
        $metrics->attach(new InterfaceMetric('Interface_I1'));
        $metrics->attach(new InterfaceMetric('Interface_I2'));
        $metrics->attach(new FileMetric('File_A.php'));
        $metrics->attach(new FileMetric('File_B.php'));
        $metrics->attach(new ProjectMetric('PROJECT_A'));
        $metrics->attach(new ProjectMetric('PROJECT_B'));
        $metrics->attach(new PackageMetric('\\Package\\A'));
        $metrics->attach(new PackageMetric('\\Package\\B'));
        $metrics->attach(new PackageMetric('\\Package\\C'));

        $metricsOfClassA = [
            'loc' => 156,
            'lloc' => 89,
            'cloc' => 77,
            'nbMethods' => 5,
            'wmc' => 7,
            'ccn' => 3,
            'bugs' => 0.15,
            'kanDefect' => 0.17,
            'relativeSystemComplexity' => 6,
            'relativeDataComplexity' => 3,
            'relativeStructuralComplexity' => 7,
            // 'volume' is ignored on purpose (test cases when metric is missing),
            'commentWeight' => 134.7,
            'intelligentContent' => 34.2,
            'lcom' => 2,
            'instability' => 0.75,
            'afferentCoupling' => 4,
            'efferentCoupling' => 3,
            'difficulty' => 0.25,
            'mi' => 103.54,
        ];
        $metricsOfClassB = [
            'loc' => 3456,
            'lloc' => 1348,
            'cloc' => 2108,
            'nbMethods' => 31,
            'wmc' => 64,
            'ccn' => 21,
            'bugs' => 9.34,
            'kanDefect' => 102.35,
            'relativeSystemComplexity' => 66,
            'relativeDataComplexity' => 93,
            'relativeStructuralComplexity' => 102,
            // 'volume' is ignored on purpose (test cases when metric is missing),
            'commentWeight' => 8173.45,
            'intelligentContent' => 270.01,
            'lcom' => 17,
            'instability' => 1,
            'afferentCoupling' => 10,
            'efferentCoupling' => 45,
            'difficulty' => 86483.26,
            'mi' => 7.12,
        ];
        $classA = $metrics->get('Class_A');
        $classB = $metrics->get('Class_B');
        foreach ($metricsOfClassA as $item => $value) {
            $classA->set($item, $value);
        }
        foreach ($metricsOfClassB as $item => $value) {
            $classB->set($item, $value);
        }

        /** @var PackageMetric $packageA */
        $packageA = $metrics->get('\\Package\\A');
        $packageA->setNormalizedDistance(65.2);
        $packageA->addIncomingClassDependency('Class_A', '\\Package\\B');
        $packageA->addIncomingClassDependency('Class_B', '\\Package\\B');
        $packageA->addIncomingClassDependency('Class_C', '\\Package\\C');
        $packageA->addIncomingClassDependency('Class_D', '\\Package\\C');
        $packageA->addOutgoingClassDependency('Class_A', '\\Package\\B');
        $packageA->addOutgoingClassDependency('Class_B', '\\Package\\B');
        $packageA->addOutgoingClassDependency('Class_C', '\\Package\\C');
        $packageA->addOutgoingClassDependency('Class_D', '\\Package\\C');
        $packageA->addClass('Class_A');
        $packageA->addClass('Class_B');
        $packageA->addClass('Class_C');
        $packageA->addClass('Class_D');

        /** @var PackageMetric $packageB */
        $packageB = $metrics->get('\\Package\\B');
        $packageB->setNormalizedDistance(12.45);

        /** @var PackageMetric $packageC */
        $packageC = $metrics->get('\\Package\\C');
        $packageC->setNormalizedDistance(0);
        $packageC->addIncomingClassDependency('Class_X', '\\Package\\A');
        $packageC->addOutgoingClassDependency('Class_X', '\\Package\\A');
        $packageC->addClass('Class_X');

        $violationsClassA = [
            Phake::mock(Violation::class),
            Phake::mock(Violation::class),
            Phake::mock(Violation::class),
        ];
        $violationsPackageB = [
            Phake::mock(Violation::class),
            Phake::mock(Violation::class),
            Phake::mock(Violation::class),
        ];
        Phake::when($violationsClassA[0])->__call('getLevel', [])->thenReturn(Violation::INFO);
        Phake::when($violationsClassA[1])->__call('getLevel', [])->thenReturn(Violation::WARNING);
        Phake::when($violationsClassA[2])->__call('getLevel', [])->thenReturn(Violation::ERROR);
        Phake::when($violationsPackageB[0])->__call('getLevel', [])->thenReturn(Violation::INFO);
        Phake::when($violationsPackageB[1])->__call('getLevel', [])->thenReturn(Violation::WARNING);
        Phake::when($violationsPackageB[2])->__call('getLevel', [])->thenReturn(Violation::CRITICAL);

        $violationsHandlerClassA = Phake::mock(ViolationsHandlerInterface::class);
        $violationsHandlerClassB = Phake::mock(ViolationsHandlerInterface::class);
        $violationsHandlerPackageA = Phake::mock(ViolationsHandlerInterface::class);
        $violationsHandlerPackageB = Phake::mock(ViolationsHandlerInterface::class);
        $violationsHandlerPackageC = Phake::mock(ViolationsHandlerInterface::class);
        Phake::when($violationsHandlerClassA)->__call('getAll', [])->thenReturn($violationsClassA);
        Phake::when($violationsHandlerClassB)->__call('getAll', [])->thenReturn([]);
        Phake::when($violationsHandlerPackageA)->__call('getAll', [])->thenReturn([]);
        Phake::when($violationsHandlerPackageB)->__call('getAll', [])->thenReturn($violationsPackageB);
        Phake::when($violationsHandlerPackageC)->__call('getAll', [])->thenReturn([]);

        $classA->set('violations', $violationsHandlerClassA);
        $classB->set('violations', $violationsHandlerClassB);
        $packageA->set('violations', $violationsHandlerPackageA);
        $packageB->set('violations', $violationsHandlerPackageB);
        $packageC->set('violations', $violationsHandlerPackageC);

        $consolidated = new Consolidated($metrics);

        $expectedAvg = (object)[
            'wmc' => 35.5,
            'ccn' => 12.0,
            'bugs' => 4.75,
            'kanDefect' => 51.26,
            'relativeSystemComplexity' => 36.0,
            'relativeDataComplexity' => 48.0,
            'relativeStructuralComplexity' => 54.5,
            'volume' => 0.0,
            'commentWeight' => 4154.08,
            'intelligentContent' => 152.11,
            'lcom' => 9.5,
            'instability' => 0.88,
            'afferentCoupling' => 7.0,
            'efferentCoupling' => 24.0,
            'difficulty' => 43241.76,
            'mi' => 55.33,
            'distance' => 18.3,
            'incomingCDep' => 1.67,
            'incomingPDep' => 1.0,
            'outgoingCDep' => 1.67,
            'outgoingPDep' => 1.0,
            'classesPerPackage' => 1.67,
        ];
        self::assertEquals($expectedAvg, $consolidated->getAvg());
        self::assertSame([$classA->all(), $classB->all()], $consolidated->getClasses());
        $expectedSum = (object)[
            'loc' => 3612,
            'cloc' => 2185,
            'lloc' => 1437,
            'nbMethods' => 36,
            'nbClasses' => 2,
            'nbInterfaces' => 2,
            'nbPackages' => 3,
            'violations' => (object)[
                'total' => 6,
                'information' => 2,
                'warning' => 2,
                'error' => 1,
                'critical' => 1,
            ],
        ];
        self::assertEquals($expectedSum, $consolidated->getSum());
        $expectedFiles = [
            'File_A.php' => ['name' => 'File_A.php'],
            'File_B.php' => ['name' => 'File_B.php'],
        ];
        self::assertSame($expectedFiles, $consolidated->getFiles());
        $expectedPackages = [
            '\\Package\\A' => $packageA->all(),
            '\\Package\\B' => $packageB->all(),
            '\\Package\\C' => $packageC->all()
        ];
        self::assertSame($expectedPackages, $consolidated->getPackages());
        $expectedProject = [
            'PROJECT_A' => ['name' => 'PROJECT_A'],
            'PROJECT_B' => ['name' => 'PROJECT_B'],
        ];
        self::assertSame($expectedProject, $consolidated->getProject());
    }
}
