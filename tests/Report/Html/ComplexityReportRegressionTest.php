<?php

namespace Test\Hal\Reporter\Html;

use DOMNode;
use Hal\Application\Config\Config;
use Hal\Component\Output\TestOutput;
use Hal\Metric\Group\Group;
use Hal\Metric\Metrics;
use Hal\Report\Html\Reporter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @group reporter
 * @group html
 */
class ComplexityReportRegressionTest extends TestCase
{
    /**
     * @dataProvider tableHeaderDataProvider
     */
    public function testComplexityHtmlReportContainsCorrectOrderOfTableColumns($junitEnabled, $expectedTableHeader)
    {
        $config = new Config();
        $output = new TestOutput();
        $reporter = new Reporter($config, $output);

        // prepares data for report
        $groups = [];
        $groups[] = new Group('group', '.*');
        $config->set('groups', $groups);

        if ($junitEnabled) {
            $config->set('junit', ['file' => '/tmp/junit.xml']);
        }

        // prepares destination
        $destination = implode(DIRECTORY_SEPARATOR, [
            sys_get_temp_dir(),
            'phpmetrics-html' . uniqid('', true)
        ]);

        $config->set('report-html', $destination);

        // generates report
        $metrics = new Metrics();
        $reporter->generate($metrics);

        // ensure complexity report contains expected table header columns
        $content = file_get_contents(sprintf('%s/complexity.html', $destination));
        $actualTableHeader = $this->getActualTableHeader($content);

        $this->assertEquals($expectedTableHeader, $actualTableHeader);
    }

    public function tableHeaderDataProvider()
    {
        $defaultTableHeader = [
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

        $junitTableHeader = array_merge($defaultTableHeader, [
            'Unit testsuites calling it'
        ]);

        return [
            'junit disabled' => [false, $defaultTableHeader],
            'junit enabled' => [true, $junitTableHeader],
        ];
    }

    private function getActualTableHeader($content)
    {
        $tableHeaderColumnNodes = (new Crawler($content))
            ->filterXPath('.//table[contains(concat(" ",normalize-space(@class)," ")," js-sort-table ")]/thead/tr')
            ->children();

        return array_map(function (DomNode $node) {
            return $node->textContent;
        }, iterator_to_array($tableHeaderColumnNodes));
    }
}
