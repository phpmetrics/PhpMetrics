<?php
namespace Test\Binary;

/**
 * @group binary
 */
class BinReportTest extends \PHPUnit\Framework\TestCase
{
    private $phar;

    public function __construct()
    {
        $this->phar = __DIR__ . '/../../bin/phpmetrics';
    }

    public function testICanRunBinFile()
    {
        $command = sprintf('%s --version', $this->phar);
        $r = shell_exec($command);
        $this->assertContains('PhpMetrics', $r);
    }

    public function testICanProvideOneDirectoryToParse()
    {
        $command = sprintf('%s --exclude="" %s 2>&1', $this->phar, __DIR__ . '/examples/1');
        $r = shell_exec($command);
        $this->assertContains('Object oriented programming', $r);
        $this->assertContains('LOC', $r);
        $this->assertRegExp('!Classes\s+2!', $r);
    }

    public function testICanProvideMultipleDirectoriesToParse()
    {
        $command = sprintf(
            '%s --exclude="" %s,%s  2>&1',
            $this->phar,
            __DIR__ . '/examples/1',
            __DIR__ . '/examples/2'
        );
        $r = shell_exec($command);
        $this->assertContains('Object oriented programming', $r);
        $this->assertContains('LOC', $r);
        $this->assertRegExp('!Classes\s+4!', $r);
    }

    public function testICanGenerateCsvReport()
    {
        $destination = '/tmp/report.csv';
        if (file_exists($destination)) {
            unlink($destination);
        }
        $command = sprintf(
            '%s --report-csv="%s" %s,%s  2>&1',
            $this->phar,
            $destination,
            __DIR__ . '/examples/1',
            __DIR__ . '/examples/2'
        );
        shell_exec($command);
        $this->assertFileExists($destination);
    }

    public function testICanGenerateJsonReport()
    {
        $destination = '/tmp/report.json';
        if (file_exists($destination)) {
            unlink($destination);
        }
        $command = sprintf(
            '%s --report-json="%s" %s,%s  2>&1',
            $this->phar,
            $destination,
            __DIR__ . '/examples/1',
            __DIR__ . '/examples/2'
        );
        shell_exec($command);
        $this->assertFileExists($destination);
        $this->assertJson(file_get_contents($destination));
    }
}
