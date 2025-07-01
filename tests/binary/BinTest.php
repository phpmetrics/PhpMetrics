<?php
namespace Test\Binary;

/**
 * @group binary
 */
class BinTest extends \PHPUnit\Framework\TestCase
{
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
        $this->assertContains('PhpMetrics', $r);
    }

    public function testICanProvideOneDirectoryToParse(): void
    {
        $command = sprintf('%s --exclude="" %s 2>&1', $this->phar, __DIR__ . '/examples/1');
        $r = shell_exec($command);
        $this->assertContains('Object oriented programming', $r);
        $this->assertContains('LOC', $r);
        $this->assertRegExp('!Classes\s+2!', $r);
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
        $this->assertContains('Object oriented programming', $r);
        $this->assertContains('LOC', $r);
        $this->assertRegExp('!Classes\s+4!', $r);
    }
}
