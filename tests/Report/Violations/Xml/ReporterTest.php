<?php
declare(strict_types=1);

namespace Tests\Hal\Report\Violations\Xml;

use DOMException;
use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\Output\Output;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Hal\Report\Violations\Xml\Reporter;
use Hal\Violation\Violation;
use Phake;
use PHPUnit\Framework\TestCase;
use function array_keys;
use function array_map;
use function dirname;
use function file_get_contents;
use function preg_quote;
use function realpath;
use function shell_exec;
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
        Phake::when($config)->__call('get', ['report-violations'])->thenReturn(null);

        $reporter = new Reporter($config, $output);
        $reporter->generate($metrics);

        Phake::verify($config)->__call('get', ['report-violations']);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoInteraction($output);
        Phake::verifyNoInteraction($metrics);
    }

    /**
     * @throws DOMException
     */
    public function testViolationsXmlReportWithNoViolationsTargetFolderExists(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $folder = realpath(dirname(__DIR__, 3)) . '/resources/report/violations/xml';
        shell_exec('rm -rf ' . $folder . '/violations.xml');
        Phake::when($config)->__call('get', ['report-violations'])->thenReturn($folder . '/violations.xml');

        $metricsList = [
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
        ];
        foreach ($metricsList as $metric) {
            Phake::when($metric)->__call('get', ['violations'])->thenReturn([]);
        }
        Phake::when($metrics)->__call('all', [])->thenReturn($metricsList);

        $reporter = new Reporter($config, $output);
        $reporter->generate($metrics);

        $xmlHeader = '<?xml version="1.0" encoding="UTF-8"?>';
        $expectedXml = <<<XML
            $xmlHeader
            <pmd version="@package_version@" timestamp="%s"/>
            XML;
        $dateFormatPattern = '[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}[+-][0-9]{2}:[0-9]{2}';

        self::assertFileExists($folder . '/violations.xml');
        self::assertMatchesRegularExpression(
            sprintf('#^' . preg_quote($expectedXml, '#') . '$#', $dateFormatPattern),
            file_get_contents($folder . '/violations.xml')
        );

        Phake::verify($config)->__call('get', ['report-violations']);
        Phake::verify($metrics)->__call('all', []);
        Phake::verify($output)->__call('writeln', ['XML report generated in "' . $folder . '/violations.xml"']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoOtherInteractions($metrics);
        shell_exec('rm -rf ' . $folder . '/violations.xml');
    }

    /**
     * @throws DOMException
     */
    public function testViolationsXmlReportWithNoViolationsTargetFolderDoesNotExist(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $folder = realpath(dirname(__DIR__, 3)) . '/resources/report/violations';
        shell_exec('rm -rf ' . $folder . '/no-existing-xml/');
        $folder .= '/no-existing-xml';
        Phake::when($config)->__call('get', ['report-violations'])->thenReturn($folder . '/violations.xml');

        $metricsList = [
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
        ];
        foreach ($metricsList as $metric) {
            Phake::when($metric)->__call('get', ['violations'])->thenReturn([]);
        }
        Phake::when($metrics)->__call('all', [])->thenReturn($metricsList);

        $reporter = new Reporter($config, $output);
        $reporter->generate($metrics);

        $xmlHeader = '<?xml version="1.0" encoding="UTF-8"?>';
        $expectedXml = <<<XML
            $xmlHeader
            <pmd version="@package_version@" timestamp="%s"/>
            XML;
        $dateFormatPattern = '[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}[+-][0-9]{2}:[0-9]{2}';

        self::assertFileExists($folder . '/violations.xml');
        self::assertMatchesRegularExpression(
            sprintf('#^' . preg_quote($expectedXml, '#') . '$#', $dateFormatPattern),
            file_get_contents($folder . '/violations.xml')
        );

        Phake::verify($config)->__call('get', ['report-violations']);
        Phake::verify($metrics)->__call('all', []);
        Phake::verify($output)->__call('writeln', ['XML report generated in "' . $folder . '/violations.xml"']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoOtherInteractions($metrics);
        $folder = realpath(dirname(__DIR__, 3)) . '/resources/report/violations';
        shell_exec('rm -rf ' . $folder . '/no-existing-xml/');
    }

    /**
     * @throws DOMException
     */
    public function testViolationsXmlReportWithViolationsAndTargetFolderExists(): void
    {
        $metrics = Phake::mock(Metrics::class);
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $folder = realpath(dirname(__DIR__, 3)) . '/resources/report/violations/xml';
        shell_exec('rm -rf ' . $folder . '/violations.xml');
        Phake::when($config)->__call('get', ['report-violations'])->thenReturn($folder . '/violations.xml');

        $metricsList = [
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
            Phake::mock(Metric::class),
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
        Phake::when($metricsList[0])->__call('get', ['violations'])->thenReturn([$violations[0], $violations[1]]);
        Phake::when($metricsList[1])->__call('get', ['name'])->thenReturn('Single violation in index 1');
        Phake::when($metricsList[1])->__call('get', ['violations'])->thenReturn([$violations[2]]);
        Phake::when($metricsList[2])->__call('get', ['name'])->thenReturn('Three violations in index 2');
        Phake::when($metricsList[2])->__call('get', ['violations'])->thenReturn(
            [$violations[3], $violations[4], $violations[5]]
        );
        foreach ($violations as $violationIndex => $violation) {
            Phake::when($violation)->__call('getName', [])->thenReturn('Violation #' . $violationIndex);
            Phake::when($violation)->__call('getDescription', [])->thenReturn('Description about ' . $violationIndex);
            Phake::when($violation)->__call('getLevel', [])->thenReturn($violationIndex % 4);
        }
        Phake::when($metrics)->__call('all', [])->thenReturn($metricsList);

        $reporter = new Reporter($config, $output);
        $reporter->generate($metrics);

        $xmlHeader = '<?xml version="1.0" encoding="UTF-8"?>';
        $expectedViolationsTags = array_map(static function (int $violationIndex): string {
            return '<violation beginLine="1" '
                . 'rule="Violation #' . $violationIndex . '" '
                . 'ruleset="Violation #' . $violationIndex . '" '
                . 'externalInfoUrl="http://www.phpmetrics.org/documentation/index.html" '
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
        $dateFormatPattern = '[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}[+-][0-9]{2}:[0-9]{2}';

        self::assertFileExists($folder . '/violations.xml');
        self::assertMatchesRegularExpression(
            sprintf('#^' . preg_quote($expectedXml, '#') . '$#', $dateFormatPattern),
            file_get_contents($folder . '/violations.xml')
        );

        Phake::verify($config)->__call('get', ['report-violations']);
        Phake::verify($metrics)->__call('all', []);
        Phake::verify($output)->__call('writeln', ['XML report generated in "' . $folder . '/violations.xml"']);
        Phake::verifyNoOtherInteractions($output);
        Phake::verifyNoOtherInteractions($config);
        Phake::verifyNoOtherInteractions($metrics);
        shell_exec('rm -rf ' . $folder . '/violations.xml');
    }
}
