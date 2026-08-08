<?php
namespace Test\Binary;

use PHPUnit\Framework\Attributes\Group;
use Polyfill\TestCaseCompatible;

/**
 * @group binary
 */
#[Group('binary')]
class PharTest extends \PHPUnit\Framework\TestCase
{

    use TestCaseCompatible;

    private $phar;

    public function setUp(): void
    {
        parent::setUp();
        $this->phar = __DIR__ . '/../../releases/phpmetrics.phar';
    }

    public function testICanRunPhar(): void
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

    public function testHtmlReportIsSelfContainedAndGeneratedWithoutWarnings(): void
    {
        $destination = sys_get_temp_dir() . '/phpmetrics-phar-report-' . uniqid('', true);
        $command = sprintf(
            '%s --exclude="" --report-html=%s %s 2>&1',
            escapeshellarg($this->phar),
            escapeshellarg($destination),
            escapeshellarg(__DIR__ . '/examples/namespaces')
        );

        $output = shell_exec($command);
        $index = file_get_contents($destination . '/index.html');
        $classes = file_get_contents($destination . '/classes.js');

        $this->assertStringNotContainsString('PHP Warning', $output);
        $this->assertStringNotContainsString('Cannot parse', $output);
        $this->assertStringContainsString('Example\\\\One\\\\First', $classes);
        $this->assertStringContainsString('src="images/phpmetrics-maintenability.png"', $index);
        $this->assertStringNotContainsString('src="http://www.phpmetrics.org', $index);
        $this->assertFileExists($destination . '/images/phpmetrics-maintenability.png');
        $this->assertFileExists($destination . '/favicon.ico');
    }
}
