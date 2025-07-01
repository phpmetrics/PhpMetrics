<?php
namespace Test\Binary;

use Polyfill\TestCaseCompatible;

/**
 * @group binary
 */
class BinReportTest extends \PHPUnit\Framework\TestCase
{
    use TestCaseCompatible;

    private $phar;

    public function setUp(): void
    {
        parent::setUp();
        $this->phar = __DIR__ . '/../../bin/phpmetrics';
    }

    public function testICanRunBinFile(): void
    {
        $command = sprintf('%s --version', $this->phar);
        $r = shell_exec($command);
        $this->assertStringContainsString('PhpMetrics', $r);
    }

    public function testICanProvideOneDirectoryToParse(): void
    {
        $command = sprintf('%s --exclude="" %s 2>&1', $this->phar, __DIR__ . '/examples/1');
        $r = shell_exec($command);
        $this->assertStringContainsString('Object oriented programming', $r);
        $this->assertStringContainsString('LOC', $r);
        $this->assertMatchesRegularExpression('!Classes\s+2!', $r);
    }

    public function testICanProvideMultipleDirectoriesToParse(): void
    {
        $command = sprintf(
            '%s --exclude="" %s,%s  2>&1',
            $this->phar,
            __DIR__ . '/examples/1',
            __DIR__ . '/examples/2'
        );
        $r = shell_exec($command);
        $this->assertStringContainsString('Object oriented programming', $r);
        $this->assertStringContainsString('LOC', $r);
        $this->assertMatchesRegularExpression('!Classes\s+4!', $r);
    }

    public function testICanGenerateCsvReport(): void
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

    public function testICanGenerateJsonReport(): void
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
