<?php
namespace Test\Binary;

/**
 * @group binary
 */
class PharTest extends \PHPUnit_Framework_TestCase
{
    private $phar;

    public function __construct()
    {
        $this->phar = __DIR__ . '/../../releases/phpmetrics.phar';
    }

    public function testICanRunPhar()
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
        $command = sprintf('%s --exclude="" %s,%s  2>&1', $this->phar, __DIR__ . '/examples/1',
            __DIR__ . '/examples/2');
        $r = shell_exec($command);
        $this->assertContains('Object oriented programming', $r);
        $this->assertContains('LOC', $r);
        $this->assertRegExp('!Classes\s+4!', $r);
    }
}
