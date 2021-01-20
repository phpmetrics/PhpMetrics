<?php

namespace Test\Hal\Reporter\Html;

use Hal\Application\Config\Config;
use Hal\Component\Output\TestOutput;
use Hal\Metric\Group\Group;
use Hal\Metric\Metrics;
use Hal\Report\Html\Reporter;
use PHPUnit\Framework\TestCase;

/**
 * @group reporter
 * @group html
 */
class ReporterTest extends TestCase
{
    public function testICanGenerateHtmlReport()
    {
        $config = new Config();
        $output = new TestOutput();
        $reporter = new Reporter($config, $output);

        // prepares data for report
        $groups = [];
        $groups[] = new Group('group1', '.*');
        $groups[] = new Group('group2', '.*');
        $config->set('groups', $groups);

        // prepares destination
        $destination = implode(DIRECTORY_SEPARATOR, [
            sys_get_temp_dir(),
            'phpmetrics-html' . uniqid('', true)
        ]);

        $config->set('report-html', $destination);

        // generates report
        $metrics = new Metrics();
        $reporter->generate($metrics);

        // ensure files are generated
        $this->assertFileExists(sprintf('%s/index.html', $destination));
        $this->assertFileExists(sprintf('%s/coupling.html', $destination));
        $this->assertFileExists(sprintf('%s/css/style.css', $destination));

        // ensure basic content is generated
        $content = file_get_contents(sprintf('%s/index.html', $destination));
        $this->assertContains('PhpMetrics report', $content);
    }
}
