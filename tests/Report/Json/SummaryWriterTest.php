<?php
declare(strict_types=1);

namespace Tests\Hal\Report\Json;

use Generator;
use Hal\Application\Config\ConfigBagInterface;
use Hal\Exception\NotWritableJsonReportException;
use Hal\Metric\ClassMetric;
use Hal\Metric\FileMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;
use Hal\Metric\ProjectMetric;
use Hal\Report\Json\SummaryWriter;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use Phake;
use PHPUnit\Framework\TestCase;
use function dirname;
use function realpath;

final class SummaryWriterTest extends TestCase
{
    public function provideMetricsToReport(): Generator
    {
        $metrics = new Metrics();
        $metrics->attach(new ProjectMetric('tree'));
        $metrics->get('tree')->set('depthOfInheritanceTree', 0);
        $expected = [
            'LOC' => [
                'linesOfCode' => 0,
                'logicalLinesOfCode' => 0,
                'commentLinesOfCode' => 0,
                'avgVolume' => 0,
                'avgCommentWeight' => 0,
                'avgIntelligentContent' => 0,
                'logicalLinesByClass' => 0.0,
                'logicalLinesByMethod' => 0.0,
            ],
            'OOP' => [
                'classes' => 0,
                'interface' => 0,
                'methods' => 0,
                'methodsByClass' => 0.0,
                'lackCohesionOfMethods' => 0,
            ],
            'Coupling' => [
                'avgAfferentCoupling' => 0,
                'avgEfferentCoupling' => 0,
                'avgInstability' => 0,
                'inheritanceTreeDepth' => 0.0,
            ],
            'Package' => [
                'packages' => 0,
                'acgClassesPerPackage' => 0,
                'avgDistance' => 0,
                'avgIncomingClassDependencies' => 0,
                'avgOutgoingClassDependencies' => 0,
                'avgIncomingPackageDependencies' => 0,
                'avgOutgoingPackageDependencies' => 0,
            ],
            'Complexity' => [
                'avgCyclomaticComplexityByClass' => 0,
                'avgWeightedMethodCountByClass' => 0,
                'avgRelativeSystemComplexity' => 0,
                'avgDifficulty' => 0,
            ],
            'Bugs' => [
                'avgBugsByClass' => 0,
                'avgDefectsByClass' => 0,
            ],
            'Violations' => [
                'critical' => 0,
                'error' => 0,
                'warning' => 0,
                'information' => 0,
            ]
        ];
        yield 'Metrics without data' => [$metrics, $expected];

        $metrics = new Metrics();
        $metrics->attach(new ProjectMetric('tree'));
        $metrics->get('tree')->set('depthOfInheritanceTree', 4.33);
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
            'volume' => 157.98,
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
            'volume' => 86045.21,
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

        $expected = [
            'LOC' => [
                'linesOfCode' => 3612,
                'logicalLinesOfCode' => 1437,
                'commentLinesOfCode' => 2185,
                'avgVolume' => 43101.6,
                'avgCommentWeight' => 4154.08,
                'avgIntelligentContent' => 4154.08,
                'logicalLinesByClass' => 718.5,
                'logicalLinesByMethod' => 39.92,
            ],
            'OOP' => [
                'classes' => 2,
                'interface' => 2,
                'methods' => 36,
                'methodsByClass' => 18.0,
                'lackCohesionOfMethods' => 9.5,
            ],
            'Coupling' => [
                'avgAfferentCoupling' => 7.0,
                'avgEfferentCoupling' => 24.0,
                'avgInstability' => 0.88,
                'inheritanceTreeDepth' => 4.33,
            ],
            'Package' => [
                'packages' => 3,
                'acgClassesPerPackage' => 1.67,
                'avgDistance' => 18.3,
                'avgIncomingClassDependencies' => 1.67,
                'avgOutgoingClassDependencies' => 1.67,
                'avgIncomingPackageDependencies' => 1.0,
                'avgOutgoingPackageDependencies' => 1.0,
            ],
            'Complexity' => [
                'avgCyclomaticComplexityByClass' => 12.0,
                'avgWeightedMethodCountByClass' => 35.5,
                'avgRelativeSystemComplexity' => 36.0,
                'avgDifficulty' => 43241.76,
            ],
            'Bugs' => [
                'avgBugsByClass' => 4.75,
                'avgDefectsByClass' => 51.26,
            ],
            'Violations' => [
                'critical' => 1,
                'error' => 1,
                'warning' => 2,
                'information' => 2,
            ]
        ];
        yield 'Metrics with data' => [$metrics, $expected];
    }

    /**
     * @dataProvider provideMetricsToReport
     * @param Metrics $metrics
     * @param array<string, mixed> $expectedOutput
     * @return void
     */
    //#[DataProvider('provideMetricsToReport')] TODO PHPUnit 10.
    public function testICanWriteSummaries(Metrics $metrics, array $expectedOutput): void
    {
        $config = Phake::mock(ConfigBagInterface::class);
        $writer = new SummaryWriter($config);
        $writer->summarize($metrics);

        self::assertSame($expectedOutput, $writer->getReport());

        Phake::verifyNoInteraction($config);
    }

    public function testGetReportFile(): void
    {
        $config = Phake::mock(ConfigBagInterface::class);
        $folder = realpath(dirname(__DIR__, 2)) . '/resources/report/json';
        Phake::when($config)->__call('has', ['quiet'])->thenReturn(false);
        Phake::when($config)->__call('get', ['report-summary-json'])->thenReturn($folder . '/report.json');

        self::assertSame($folder . '/report.json', (new SummaryWriter($config))->getReportFile());

        Phake::verify($config)->__call('has', ['quiet']);
        Phake::verify($config)->__call('get', ['report-summary-json']);
        Phake::verifyNoOtherInteractions($config);
    }

    public function testThereIsNoReportFileIfQuiet(): void
    {
        $config = Phake::mock(ConfigBagInterface::class);
        Phake::when($config)->__call('has', ['quiet'])->thenReturn(true);

        self::assertFalse((new SummaryWriter($config))->getReportFile());

        Phake::verify($config)->__call('has', ['quiet']);
        Phake::verifyNoOtherInteractions($config);
    }

    public function testThereIsNoReportFileIfNoReportFileSetInConfig(): void
    {
        $config = Phake::mock(ConfigBagInterface::class);
        Phake::when($config)->__call('has', ['quiet'])->thenReturn(false);
        Phake::when($config)->__call('get', ['report-summary-json'])->thenReturn(false);

        self::assertFalse((new SummaryWriter($config))->getReportFile());

        Phake::verify($config)->__call('has', ['quiet']);
        Phake::verify($config)->__call('get', ['report-summary-json']);
        Phake::verifyNoOtherInteractions($config);
    }

    public function testThereIsNoReportFileWhenFolderIsNotWriteable(): void
    {
        $config = Phake::mock(ConfigBagInterface::class);
        $folder = realpath(dirname(__DIR__, 2)) . '/resources/report/no-perm-json';
        Phake::when($config)->__call('has', ['quiet'])->thenReturn(false);
        Phake::when($config)->__call('get', ['report-summary-json'])->thenReturn($folder . '/report.json');

        $this->expectExceptionObject(NotWritableJsonReportException::noPermission($folder . '/report.json'));

        (new SummaryWriter($config))->getReportFile();

        Phake::verify($config)->__call('has', ['quiet']);
        Phake::verify($config)->__call('get', ['report-summary-json']);
        Phake::verifyNoOtherInteractions($config);
    }
}
