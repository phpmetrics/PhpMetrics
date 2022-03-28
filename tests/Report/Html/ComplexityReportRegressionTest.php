<?php
declare(strict_types=1);

namespace Tests\Hal\Report\Html;

use DOMNode;
use Generator;
use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\Output\Output;
use Hal\Metric\Group\Group;
use Hal\Metric\Metrics;
use Hal\Report\Html\Reporter;
use JsonException;
use Phake;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;
use function array_map;
use function file_get_contents;
use function implode;
use function iterator_to_array;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;
use const DIRECTORY_SEPARATOR;

final class ComplexityReportRegressionTest extends TestCase
{
    /**
     * @return Generator<string, array{0: bool, 1: array<string>}>
     */
    public function provideTableHeader(): Generator
    {
        $tableHeader = [
            'Class',
            'WMC',
            'Class cycl.',
            'Max method cycl.',
            'Relative system complexity',
            'Relative data complexity',
            'Relative structural complexity',
            'Bugs',
            'Defects',
        ];
        yield 'junit disabled' => [false, $tableHeader];

        $tableHeader[] = 'Unit testsuites calling it';
        yield 'junit enabled' => [true, $tableHeader];
    }

    /**
     * @dataProvider provideTableHeader
     * @param bool $junitEnabled
     * @param array<string> $expectedTableHeader
     * @return void
     * @throws JsonException
     */
    //#[DataProvider('provideTableHeader')] TODO: PHPUnit 10.
    public function testComplexityHtmlReportContainsCorrectOrderOfTableColumns(
        bool $junitEnabled,
        array $expectedTableHeader
    ): void {
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $reporter = new Reporter($config, $output);

        // prepares data for report
        $groups = [new Group('group', '.*')];
        Phake::when($config)->__call('get', ['groups'])->thenReturn($groups);

        if ($junitEnabled) {
            Phake::when($config)->__call('get', ['junit'])->thenReturn(['file' => '/tmp/junit.xml']);
            Phake::when($config)->__call('has', ['junit'])->thenReturn(true);
        }

        // prepares destination
        $destination = implode(DIRECTORY_SEPARATOR, [sys_get_temp_dir(), 'phpmetrics-html' . uniqid('', true)]);
        Phake::when($config)->__call('get', ['report-html'])->thenReturn($destination);

        // generates report
        $reporter->generate(new Metrics());

        // ensure complexity report contains expected table header columns
        $content = file_get_contents(sprintf('%s/complexity.html', $destination));
        $actualTableHeader = $this->getActualTableHeader($content);

        self::assertSame($expectedTableHeader, $actualTableHeader);
    }

    /**
     * @param string $content
     * @return array<string>
     */
    private function getActualTableHeader(string $content): array
    {
        $headerColumns = (new Crawler($content))
            ->filterXPath('.//table[contains(concat(" ",normalize-space(@class)," ")," js-sort-table ")]/thead/tr')
            ->children();

        return array_map(static fn (DOMNode $node): string => $node->textContent, iterator_to_array($headerColumns));
    }
}
