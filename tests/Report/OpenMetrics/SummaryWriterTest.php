<?php
declare(strict_types=1);

namespace Tests\Hal\Report\OpenMetrics;

use Generator;
use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\File\WriterInterface;
use Hal\Exception\NotWritableOpenMetricsReportException;
use Hal\Metric\ClassMetric;
use Hal\Metric\FileMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;
use Hal\Metric\ProjectMetric;
use Hal\Report\OpenMetrics\SummaryWriter;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use Phake;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SummaryWriterTest extends TestCase
{
    /**
     * @return Generator<string, array{Metrics, array<string, array<string, int|float>>}>
     */
    public static function provideMetricsToReport(): Generator
    {
        $metrics = new Metrics();
        $metrics->attach(new ProjectMetric('tree'));
        $metrics->get('tree')->set('depthOfInheritanceTree', 0);
        $expected = <<<'OM'
# TYPE lines_of_code gauge
# HELP lines_of_code Lines of code
lines_of_code 0.000000
# TYPE logical_lines_of_code gauge
# HELP logical_lines_of_code Logical lines of code
logical_lines_of_code 0.000000
# TYPE comment_lines_of_code gauge
# HELP comment_lines_of_code Comment lines of code
comment_lines_of_code 0.000000
# TYPE average_volume gauge
# HELP average_volume Average volume
average_volume 0.000000
# TYPE average_comment_weight gauge
# HELP average_comment_weight Average comment weight
average_comment_weight 0.000000
# TYPE average_intelligent_content gauge
# HELP average_intelligent_content Average intelligent content
average_intelligent_content 0.000000
# TYPE loc_by_class gauge
# HELP loc_by_class Logical lines of code by class
loc_by_class 0.000000
# TYPE loc_by_method gauge
# HELP loc_by_method Logical lines of code by method
loc_by_method 0.000000
# TYPE number_of_classes gauge
# HELP number_of_classes Number of classes
number_of_classes 0.000000
# TYPE number_of_interfaces gauge
# HELP number_of_interfaces Number of interfaces
number_of_interfaces 0.000000
# TYPE number_of_methods gauge
# HELP number_of_methods Number of methods
number_of_methods 0.000000
# TYPE methods_by_class gauge
# HELP methods_by_class Methods by class
methods_by_class 0.000000
# TYPE lack_cohesion_methods gauge
# HELP lack_cohesion_methods Lack of cohesion of methods
lack_cohesion_methods 0.000000
# TYPE afferent_coupling gauge
# HELP afferent_coupling Average afferent coupling
afferent_coupling 0.000000
# TYPE efferent_coupling gauge
# HELP efferent_coupling Average efferent coupling
efferent_coupling 0.000000
# TYPE instability gauge
# HELP instability Average instability
instability 0.000000
# TYPE tree_inheritance_depth gauge
# HELP tree_inheritance_depth Depth of inheritance tree
tree_inheritance_depth 0.000000
# TYPE number_of_packages gauge
# HELP number_of_packages Number of packages
number_of_packages 0.000000
# TYPE classes_by_package gauge
# HELP classes_by_package Average classes by package
classes_by_package 0.000000
# TYPE distance gauge
# HELP distance Average distance
distance 0.000000
# TYPE incoming_class_dependencies gauge
# HELP incoming_class_dependencies Average incoming class dependencies
incoming_class_dependencies 0.000000
# TYPE outgoing_class_dependencies gauge
# HELP outgoing_class_dependencies Average outgoing class dependencies
outgoing_class_dependencies 0.000000
# TYPE incoming_package_dependencies gauge
# HELP incoming_package_dependencies Average incoming package dependencies
incoming_package_dependencies 0.000000
# TYPE outgoing_package_dependencies gauge
# HELP outgoing_package_dependencies Average outgoing package dependencies
outgoing_package_dependencies 0.000000
# TYPE cyclomatic_complexity_by_class gauge
# HELP cyclomatic_complexity_by_class Average cyclomatic complexity by class
cyclomatic_complexity_by_class 0.000000
# TYPE weighted_method_count_by_class gauge
# HELP weighted_method_count_by_class Average weighted method count by class
weighted_method_count_by_class 0.000000
# TYPE relative_system_complexity gauge
# HELP relative_system_complexity Average relative system complexity
relative_system_complexity 0.000000
# TYPE difficulty gauge
# HELP difficulty Average difficulty
difficulty 0.000000
# TYPE bugs_by_class gauge
# HELP bugs_by_class Average bugs by class
bugs_by_class 0.000000
# TYPE defects_by_class gauge
# HELP defects_by_class Average defects by class (Kan)
defects_by_class 0.000000
# TYPE critical_violations gauge
# HELP critical_violations Critical violations
critical_violations 0.000000
# TYPE error_violations gauge
# HELP error_violations Error violations
error_violations 0.000000
# TYPE warning_violations gauge
# HELP warning_violations Warning violations
warning_violations 0.000000
# TYPE information_violations gauge
# HELP information_violations Information violations
information_violations 0.000000
# EOF

OM;
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

        $expected = <<<'OM'
# TYPE lines_of_code gauge
# HELP lines_of_code Lines of code
lines_of_code 3612.000000
# TYPE logical_lines_of_code gauge
# HELP logical_lines_of_code Logical lines of code
logical_lines_of_code 1437.000000
# TYPE comment_lines_of_code gauge
# HELP comment_lines_of_code Comment lines of code
comment_lines_of_code 2185.000000
# TYPE average_volume gauge
# HELP average_volume Average volume
average_volume 43101.600000
# TYPE average_comment_weight gauge
# HELP average_comment_weight Average comment weight
average_comment_weight 4154.080000
# TYPE average_intelligent_content gauge
# HELP average_intelligent_content Average intelligent content
average_intelligent_content 152.110000
# TYPE loc_by_class gauge
# HELP loc_by_class Logical lines of code by class
loc_by_class 718.500000
# TYPE loc_by_method gauge
# HELP loc_by_method Logical lines of code by method
loc_by_method 39.920000
# TYPE number_of_classes gauge
# HELP number_of_classes Number of classes
number_of_classes 2.000000
# TYPE number_of_interfaces gauge
# HELP number_of_interfaces Number of interfaces
number_of_interfaces 2.000000
# TYPE number_of_methods gauge
# HELP number_of_methods Number of methods
number_of_methods 36.000000
# TYPE methods_by_class gauge
# HELP methods_by_class Methods by class
methods_by_class 18.000000
# TYPE lack_cohesion_methods gauge
# HELP lack_cohesion_methods Lack of cohesion of methods
lack_cohesion_methods 9.500000
# TYPE afferent_coupling gauge
# HELP afferent_coupling Average afferent coupling
afferent_coupling 7.000000
# TYPE efferent_coupling gauge
# HELP efferent_coupling Average efferent coupling
efferent_coupling 24.000000
# TYPE instability gauge
# HELP instability Average instability
instability 0.880000
# TYPE tree_inheritance_depth gauge
# HELP tree_inheritance_depth Depth of inheritance tree
tree_inheritance_depth 4.330000
# TYPE number_of_packages gauge
# HELP number_of_packages Number of packages
number_of_packages 3.000000
# TYPE classes_by_package gauge
# HELP classes_by_package Average classes by package
classes_by_package 1.670000
# TYPE distance gauge
# HELP distance Average distance
distance 18.300000
# TYPE incoming_class_dependencies gauge
# HELP incoming_class_dependencies Average incoming class dependencies
incoming_class_dependencies 1.670000
# TYPE outgoing_class_dependencies gauge
# HELP outgoing_class_dependencies Average outgoing class dependencies
outgoing_class_dependencies 1.670000
# TYPE incoming_package_dependencies gauge
# HELP incoming_package_dependencies Average incoming package dependencies
incoming_package_dependencies 1.000000
# TYPE outgoing_package_dependencies gauge
# HELP outgoing_package_dependencies Average outgoing package dependencies
outgoing_package_dependencies 1.000000
# TYPE cyclomatic_complexity_by_class gauge
# HELP cyclomatic_complexity_by_class Average cyclomatic complexity by class
cyclomatic_complexity_by_class 12.000000
# TYPE weighted_method_count_by_class gauge
# HELP weighted_method_count_by_class Average weighted method count by class
weighted_method_count_by_class 35.500000
# TYPE relative_system_complexity gauge
# HELP relative_system_complexity Average relative system complexity
relative_system_complexity 36.000000
# TYPE difficulty gauge
# HELP difficulty Average difficulty
difficulty 43241.760000
# TYPE bugs_by_class gauge
# HELP bugs_by_class Average bugs by class
bugs_by_class 4.750000
# TYPE defects_by_class gauge
# HELP defects_by_class Average defects by class (Kan)
defects_by_class 51.260000
# TYPE critical_violations gauge
# HELP critical_violations Critical violations
critical_violations 1.000000
# TYPE error_violations gauge
# HELP error_violations Error violations
error_violations 1.000000
# TYPE warning_violations gauge
# HELP warning_violations Warning violations
warning_violations 2.000000
# TYPE information_violations gauge
# HELP information_violations Information violations
information_violations 2.000000
# EOF

OM;
        yield 'Metrics with data' => [$metrics, $expected];
    }

    /**
     * @param Metrics $metrics
     * @param string $expectedOutput
     * @return void
     */
    #[DataProvider('provideMetricsToReport')]
    public function testICanWriteSummaries(Metrics $metrics, string $expectedOutput): void
    {
        $config = Phake::mock(ConfigBagInterface::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        $writer = new SummaryWriter($config, $fileWriter);
        $writer->summarize($metrics);

        self::assertSame($expectedOutput, $writer->getReport());

        Phake::verifyNoInteraction($fileWriter);
        Phake::verifyNoInteraction($config);
    }

    public function testGetReportFile(): void
    {
        $config = Phake::mock(ConfigBagInterface::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        $file = '/test/report/summary/report.txt';
        Phake::when($config)->__call('has', ['quiet'])->thenReturn(false);
        Phake::when($config)->__call('get', ['report-openmetrics'])->thenReturn($file);
        Phake::when($fileWriter)->__call('exists', ['/test/report/summary'])->thenReturn(true);
        Phake::when($fileWriter)->__call('isWritable', ['/test/report/summary'])->thenReturn(true);

        self::assertSame($file, (new SummaryWriter($config, $fileWriter))->getReportFile());

        Phake::verify($config)->__call('has', ['quiet']);
        Phake::verify($config)->__call('get', ['report-openmetrics']);
        Phake::verify($fileWriter)->__call('exists', ['/test/report/summary']);
        Phake::verify($fileWriter)->__call('isWritable', ['/test/report/summary']);
        Phake::verifyNoOtherInteractions($fileWriter);
        Phake::verifyNoOtherInteractions($config);
    }

    public function testThereIsNoReportFileIfQuiet(): void
    {
        $config = Phake::mock(ConfigBagInterface::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        Phake::when($config)->__call('has', ['quiet'])->thenReturn(true);

        self::assertFalse((new SummaryWriter($config, $fileWriter))->getReportFile());

        Phake::verify($config)->__call('has', ['quiet']);
        Phake::verifyNoInteraction($fileWriter);
        Phake::verifyNoOtherInteractions($config);
    }

    public function testThereIsNoReportFileIfNoReportFileSetInConfig(): void
    {
        $config = Phake::mock(ConfigBagInterface::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        Phake::when($config)->__call('has', ['quiet'])->thenReturn(false);
        Phake::when($config)->__call('get', ['report-openmetrics'])->thenReturn(null);

        self::assertFalse((new SummaryWriter($config, $fileWriter))->getReportFile());

        Phake::verify($config)->__call('has', ['quiet']);
        Phake::verify($config)->__call('get', ['report-openmetrics']);
        Phake::verifyNoInteraction($fileWriter);
        Phake::verifyNoOtherInteractions($config);
    }

    public function testThereIsNoReportFileWhenFolderDoesNotExist(): void
    {
        $config = Phake::mock(ConfigBagInterface::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        $file = '/test/report/summary/report.txt';
        Phake::when($config)->__call('has', ['quiet'])->thenReturn(false);
        Phake::when($config)->__call('get', ['report-openmetrics'])->thenReturn($file);
        Phake::when($fileWriter)->__call('exists', ['/test/report/summary'])->thenReturn(false);

        $this->expectExceptionObject(NotWritableOpenMetricsReportException::noPermission($file));

        (new SummaryWriter($config, $fileWriter))->getReportFile();

        Phake::verify($config)->__call('has', ['quiet']);
        Phake::verify($config)->__call('get', ['report-openmetrics']);
        Phake::verify($fileWriter)->__call('exists', ['/test/report/summary']);
        Phake::verifyNoOtherInteractions($fileWriter);
        Phake::verifyNoOtherInteractions($config);
    }

    public function testThereIsNoReportFileWhenFolderIsNotWritable(): void
    {
        $config = Phake::mock(ConfigBagInterface::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        $file = '/test/report/summary/report.txt';
        Phake::when($config)->__call('has', ['quiet'])->thenReturn(false);
        Phake::when($config)->__call('get', ['report-openmetrics'])->thenReturn($file);
        Phake::when($fileWriter)->__call('exists', ['/test/report/summary'])->thenReturn(true);
        Phake::when($fileWriter)->__call('isWritable', ['/test/report/summary'])->thenReturn(false);

        $this->expectExceptionObject(NotWritableOpenMetricsReportException::noPermission($file));

        (new SummaryWriter($config, $fileWriter))->getReportFile();

        Phake::verify($config)->__call('has', ['quiet']);
        Phake::verify($config)->__call('get', ['report-openmetrics']);
        Phake::verify($fileWriter)->__call('exists', ['/test/report/summary']);
        Phake::verify($fileWriter)->__call('isWritable', ['/test/report/summary']);
        Phake::verifyNoOtherInteractions($fileWriter);
        Phake::verifyNoOtherInteractions($config);
    }
}
