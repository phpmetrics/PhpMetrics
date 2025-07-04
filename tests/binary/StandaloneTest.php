<?php
namespace Test\Hal\binary;

use PHPUnit\Framework\Attributes\Group;
use Polyfill\TestCaseCompatible;

/**
 * @group binary
 */
#[Group('binary')]
class StandaloneTest extends \PHPUnit\Framework\TestCase
{

    use TestCaseCompatible;

    private $binary;

    public function setUp(): void
    {
        parent::setUp();
        $this->binary = __DIR__ . '/../../releases/phpmetrics-linux-x86_64';
    }

    public function testICanRunStandaloneBinary(): void
    {
        $command = sprintf('%s --version', $this->binary);
        $r = shell_exec($command);
        $this->assertStringContainsString('PhpMetrics', $r);
    }

    public function testICanProvideOneDirectoryToParse(): void
    {
        $command = sprintf('%s --exclude="" %s 2>&1', $this->binary, __DIR__ . '/examples/1');
        $r = shell_exec($command);
        $this->assertStringContainsString('Object oriented programming', $r);
        $this->assertStringContainsString('LOC', $r);
        $this->assertMatchesRegularExpression('!Classes\s+2!', $r);
    }

    public function testICanProvideMultipleDirectoriesToParse(): void
    {
        $command = sprintf(
            '%s --exclude="" %s,%s  2>&1',
            $this->binary,
            __DIR__ . '/examples/1',
            __DIR__ . '/examples/2'
        );
        $r = shell_exec($command);
        $this->assertStringContainsString('Object oriented programming', $r);
        $this->assertStringContainsString('LOC', $r);
        $this->assertMatchesRegularExpression('!Classes\s+4!', $r);
    }
}
