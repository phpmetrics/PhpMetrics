<?php
declare(strict_types=1);

namespace Tests\Hal\Report\Cli;

use Generator;
use Hal\Application\Config\ConfigBagInterface;
use Hal\Metric\ClassMetric;
use Hal\Metric\FileMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;
use Hal\Metric\ProjectMetric;
use Hal\Report\Cli\SummaryWriter;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use Phake;
use Phake\IMock;
use PHPUnit\Framework\TestCase;

final class SummaryWriterTest extends TestCase
{
    public function provideMetricsToReport(): Generator
    {
        $metrics = new Metrics();
        $metrics->attach(new ProjectMetric('tree'));
        $metrics->get('tree')->set('depthOfInheritanceTree', 0);
        $config = Phake::mock(ConfigBagInterface::class);
        Phake::when($config)->__call('has', ['junit'])->thenReturn(false);
        Phake::when($config)->__call('has', ['quiet'])->thenReturn(false);
        $expected = <<<EOT
            LOC
                Lines of code                               0
                Logical lines of code                       0
                Comment lines of code                       0
                Average volume                              0
                Average comment weight                      0
                Average intelligent content                 0
                Logical lines of code by class              0
                Logical lines of code by method             0
            
            Object oriented programming
                Classes                                     0
                Interface                                   0
                Methods                                     0
                Methods by class                            0
                Lack of cohesion of methods                 0
                
            Coupling
                Average afferent coupling                   0
                Average efferent coupling                   0
                Average instability                         0
                Depth of Inheritance Tree                   0
                
            Package
                Packages                                    0
                Average classes per package                 0
                Average distance                            0
                Average incoming class dependencies         0
                Average outgoing class dependencies         0
                Average incoming package dependencies       0
                Average outgoing package dependencies       0
            
            Complexity
                Average Cyclomatic complexity by class      0
                Average Weighted method count by class      0
                Average Relative system complexity          0
                Average Difficulty                          0
                
            Bugs
                Average bugs by class                       0
                Average defects by class (Kan)              0
            
            Violations
                Critical                                    0
                Error                                       0
                Warning                                     0
                Information                                 0
            
            
            
            EOT;
        yield 'Metrics without data / JUnit disabled' => [$metrics, $config, $expected];

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

        $config = Phake::mock(ConfigBagInterface::class);
        Phake::when($config)->__call('has', ['junit'])->thenReturn(false);

        $expected = <<<EOT
            LOC
                Lines of code                               3612
                Logical lines of code                       1437
                Comment lines of code                       2185
                Average volume                              43101.6
                Average comment weight                      4154.08
                Average intelligent content                 4154.08
                Logical lines of code by class              718.5
                Logical lines of code by method             39.92
            
            Object oriented programming
                Classes                                     2
                Interface                                   2
                Methods                                     36
                Methods by class                            18
                Lack of cohesion of methods                 9.5
                
            Coupling
                Average afferent coupling                   7
                Average efferent coupling                   24
                Average instability                         0.88
                Depth of Inheritance Tree                   4.33
                
            Package
                Packages                                    3
                Average classes per package                 1.67
                Average distance                            18.3
                Average incoming class dependencies         1.67
                Average outgoing class dependencies         1.67
                Average incoming package dependencies       1
                Average outgoing package dependencies       1
            
            Complexity
                Average Cyclomatic complexity by class      12
                Average Weighted method count by class      35.5
                Average Relative system complexity          36
                Average Difficulty                          43241.76
                
            Bugs
                Average bugs by class                       4.75
                Average defects by class (Kan)              51.26
            
            Violations
                Critical                                    1
                Error                                       1
                Warning                                     2
                Information                                 2
            
            
            
            EOT;
        yield 'Metrics with data / JUnit disabled' => [$metrics, $config, $expected];

        $metrics = new Metrics();
        $metrics->attach(new ProjectMetric('tree'));
        $metrics->get('tree')->set('depthOfInheritanceTree', 0);
        $metrics->attach(new ProjectMetric('unitTesting'));
        $metrics->get('unitTesting')->set('nbSuites', 0);
        $metrics->get('unitTesting')->set('nbCoveredClasses', 0);
        $metrics->get('unitTesting')->set('percentCoveredClasses', 0);
        $config = Phake::mock(ConfigBagInterface::class);
        Phake::when($config)->__call('has', ['junit'])->thenReturn(true);
        $expected = <<<EOT
            LOC
                Lines of code                               0
                Logical lines of code                       0
                Comment lines of code                       0
                Average volume                              0
                Average comment weight                      0
                Average intelligent content                 0
                Logical lines of code by class              0
                Logical lines of code by method             0
            
            Object oriented programming
                Classes                                     0
                Interface                                   0
                Methods                                     0
                Methods by class                            0
                Lack of cohesion of methods                 0
                
            Coupling
                Average afferent coupling                   0
                Average efferent coupling                   0
                Average instability                         0
                Depth of Inheritance Tree                   0
                
            Package
                Packages                                    0
                Average classes per package                 0
                Average distance                            0
                Average incoming class dependencies         0
                Average outgoing class dependencies         0
                Average incoming package dependencies       0
                Average outgoing package dependencies       0
            
            Complexity
                Average Cyclomatic complexity by class      0
                Average Weighted method count by class      0
                Average Relative system complexity          0
                Average Difficulty                          0
                
            Bugs
                Average bugs by class                       0
                Average defects by class (Kan)              0
            
            Violations
                Critical                                    0
                Error                                       0
                Warning                                     0
                Information                                 0
                        
            Unit testing
                Number of unit tests                        0
                Classes called by tests                     0
                Classes called by tests (percent)           0 %
            
            
            EOT;
        yield 'Metrics without data / JUnit enabled' => [$metrics, $config, $expected];
    }

    /**
     * @dataProvider provideMetricsToReport
     * @param Metrics $metrics
     * @param ConfigBagInterface&IMock $config
     * @param string $expectedOutput
     * @return void
     */
    //#[DataProvider('provideMetricsToReport')] TODO PHPUnit 10.
    public function testICanWriteSummaries(
        Metrics $metrics,
        ConfigBagInterface&IMock $config,
        string $expectedOutput
    ): void {
        $writer = new SummaryWriter($config);
        $writer->summarize($metrics);

        self::assertTrue($writer->getReportFile());
        self::assertSame($expectedOutput, $writer->getReport());

        Phake::verify($config)->__call('has', ['junit']);
        Phake::verify($config)->__call('has', ['quiet']);
        Phake::verifyNoOtherInteractions($config);
    }

    public function testGetReportFile(): void
    {
        $config = Phake::mock(ConfigBagInterface::class);
        Phake::when($config)->__call('has', ['quiet'])->thenReturn(true);

        self::assertFalse((new SummaryWriter($config))->getReportFile());
    }
}
