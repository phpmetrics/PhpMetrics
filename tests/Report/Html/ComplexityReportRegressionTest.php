<?php
declare(strict_types=1);

namespace Tests\Hal\Report\Html;

use DOMNode;
use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\Output\Output;
use Hal\Metric\Group\Group;
use Hal\Metric\Metrics;
use Hal\Report\Html\Reporter;
use Hal\Report\Html\ViewHelper;
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
     * @return void
     * @throws JsonException
     */
    public function testComplexityHtmlReportContainsCorrectOrderOfTableColumns(): void
    {
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $reporter = new Reporter($config, $output, new ViewHelper());

        // prepares data for report
        $groups = [new Group('group', '.*')];
        Phake::when($config)->__call('get', ['groups'])->thenReturn($groups);

        // prepares destination
        $destination = implode(DIRECTORY_SEPARATOR, [sys_get_temp_dir(), 'phpmetrics-html' . uniqid('', true)]);
        Phake::when($config)->__call('get', ['report-html'])->thenReturn($destination);

        // generates report
        $reporter->generate(new Metrics());

        // ensure complexity report contains expected table header columns
        $content = file_get_contents(sprintf('%s/complexity.html', $destination));
        $actualTableHeader = $this->getActualTableHeader($content);
        $expectedTableHeader = [
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
