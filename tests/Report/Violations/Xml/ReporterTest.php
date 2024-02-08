<?php
/** @noinspection PhpComposerExtensionStubsInspection As ext-dom is not required but suggested. */
declare(strict_types=1);

namespace Tests\Hal\Report\Violations\Xml;

use DOMException;
use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\File\WriterInterface;
use Hal\Component\Output\Output;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Hal\Report\Violations\Xml\Reporter;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use Phake;
use PHPUnit\Framework\TestCase;
use function array_keys;
use function array_map;
use function preg_quote;
use function sprintf;

final class ReporterTest extends TestCase
{
    /**
     * @throws DOMException
     */
    public function testViolationsXmlReportIsIgnoredWhenConfigIsNotSet(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        Phake::when($config)->__call('get', ['report-violations'])->thenReturn(null);

        $reporter = new Reporter($config, $output, $fileWriter);
        $reporter->generate($metrics);

        Phake::verify($config)->__call('get', ['report-violations']);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoInteraction($fileWriter);
        Phake::verifyNoInteraction($output);
        Phake::verifyNoInteraction($metrics);
    }

    /**
     * @throws DOMException
     */
    public function testViolationsXmlReportWithoutViolation(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        $violationsHandler = Phake::mock(ViolationsHandlerInterface::class);
        $file = '/test/report/violations.xml';
        Phake::when($config)->__call('get', ['report-violations'])->thenReturn($file);
        Phake::when($fileWriter)->__call('ensureDirectoryExists', ['/test/report'])->thenDoNothing();
        Phake::when($fileWriter)->__call('write', [$file, Phake::ignoreRemaining()])->thenDoNothing();

        $metricsList = [
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
        ];
        foreach ($metricsList as $metric) {
            Phake::when($metric)->__call('get', ['violations'])->thenReturn($violationsHandler);
            Phake::when($violationsHandler)->__call('getAll', [])->thenReturn([]);
        }
        Phake::when($metrics)->__call('all', [])->thenReturn($metricsList);

        $reporter = new Reporter($config, $output, $fileWriter);
        $reporter->generate($metrics);

        Phake::verify($config)->__call('get', ['report-violations']);
        Phake::verify($metrics)->__call('all', []);
        Phake::verify($fileWriter)->__call('ensureDirectoryExists', ['/test/report']);
        $verifyWrite = Phake::verify($fileWriter)->__call('write', [$file, Phake::ignoreRemaining()]);
        $xmlHeader = '<?xml version="1.0" encoding="UTF-8"?>';
        $expectedXml = <<<XML
            $xmlHeader
            <pmd version="@package_version@" timestamp="%s"/>
            XML;
        $actualXml = $verifyWrite[0]->getCall()->getArguments()[1];
        self::assertXmlMatches($expectedXml, $actualXml);
        Phake::verify($output)->__call('writeln', ['XML report generated in "' . $file . '".']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoOtherInteractions($fileWriter);
        Phake::verifyNoOtherInteractions($metrics);
    }

    /**
     * @throws DOMException
     */
    public function testViolationsXmlReportWithViolations(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        $file = '/test/report/violations.xml';
        Phake::when($config)->__call('get', ['report-violations'])->thenReturn($file);
        Phake::when($fileWriter)->__call('ensureDirectoryExists', ['/test/report'])->thenDoNothing();
        Phake::when($fileWriter)->__call('write', [$file, Phake::ignoreRemaining()])->thenDoNothing();

        $metricsList = [
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
        ];
        $violationsHandlers = [
            Phake::mock(ViolationsHandlerInterface::class),
            Phake::mock(ViolationsHandlerInterface::class),
            Phake::mock(ViolationsHandlerInterface::class),
        ];
        $violations = [
            Phake::mock(Violation::class),
            Phake::mock(Violation::class),
            Phake::mock(Violation::class),
            Phake::mock(Violation::class),
            Phake::mock(Violation::class),
            Phake::mock(Violation::class),
        ];
        Phake::when($metricsList[0])->__call('get', ['name'])->thenReturn('Two violations in index 0');
        Phake::when($metricsList[0])->__call('get', ['violations'])->thenReturn($violationsHandlers[0]);
        Phake::when($violationsHandlers[0])->__call('getAll', [])->thenReturn([$violations[0], $violations[1]]);
        Phake::when($metricsList[1])->__call('get', ['name'])->thenReturn('Single violation in index 1');
        Phake::when($metricsList[1])->__call('get', ['violations'])->thenReturn($violationsHandlers[1]);
        Phake::when($violationsHandlers[1])->__call('getAll', [])->thenReturn([$violations[2]]);
        Phake::when($metricsList[2])->__call('get', ['name'])->thenReturn('Three violations in index 2');
        Phake::when($metricsList[2])->__call('get', ['violations'])->thenReturn($violationsHandlers[2]);
        Phake::when($violationsHandlers[2])->__call('getAll', [])->thenReturn([$violations[3], $violations[4], $violations[5]]);

        foreach ($violations as $violationIndex => $violation) {
            Phake::when($violation)->__call('getName', [])->thenReturn('Violation #' . $violationIndex);
            Phake::when($violation)->__call('getDescription', [])->thenReturn('Description about ' . $violationIndex);
            Phake::when($violation)->__call('getLevel', [])->thenReturn($violationIndex % 4);
        }
        Phake::when($metrics)->__call('all', [])->thenReturn($metricsList);

        $reporter = new Reporter($config, $output, $fileWriter);
        $reporter->generate($metrics);

        $xmlHeader = '<?xml version="1.0" encoding="UTF-8"?>';
        $expectedViolationsTags = array_map(static function (int $violationIndex): string {
            return '<violation beginLine="1" '
                . 'rule="Violation #' . $violationIndex . '" '
                . 'ruleset="Violation #' . $violationIndex . '" '
                . 'externalInfoUrl="https://www.phpmetrics.org" '
                . 'priority="' . 4 - ($violationIndex % 4) . '">'
                . 'Description about ' . $violationIndex
                . '</violation>';
        }, array_keys($violations));

        $expectedXml = <<<XML
            $xmlHeader
            <pmd version="@package_version@" timestamp="%s">
              <file name="Two violations in index 0">
                $expectedViolationsTags[0]
                $expectedViolationsTags[1]
              </file>
              <file name="Single violation in index 1">
                $expectedViolationsTags[2]
              </file>
              <file name="Three violations in index 2">
                $expectedViolationsTags[3]
                $expectedViolationsTags[4]
                $expectedViolationsTags[5]
              </file>
            </pmd>

            XML;

        Phake::verify($config)->__call('get', ['report-violations']);
        Phake::verify($metrics)->__call('all', []);
        Phake::verify($fileWriter)->__call('ensureDirectoryExists', ['/test/report']);
        $verifyWrite = Phake::verify($fileWriter)->__call('write', [$file, Phake::ignoreRemaining()]);
        $actualXml = $verifyWrite[0]->getCall()->getArguments()[1];
        self::assertXmlMatches($expectedXml, $actualXml);
        Phake::verify($output)->__call('writeln', ['XML report generated in "' . $file . '".']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoOtherInteractions($fileWriter);
        Phake::verifyNoOtherInteractions($metrics);
    }

    /**
     * Ensure the expected XML pattern (modified because of the management of the date in the XML content) matches the
     * actual saved XML by the original method.
     *
     * @param string $expected
     * @param string $actual
     * @return void
     */
    private static function assertXmlMatches(string $expected, string $actual): void
    {
        $dateFormatPattern = '[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}[+-][0-9]{2}:[0-9]{2}';
        $expectedPattern = sprintf('#^' . preg_quote($expected, '#') . '$#', $dateFormatPattern);
        self::assertMatchesRegularExpression($expectedPattern, $actual);
    }
}
