<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Phpmetrix\Console\CliInput;

/**
 * @covers Phpmetrix\Console\CliInput
 */
final class CliInputTest extends TestCase
{

    public function testDirOnly()
    {
        $cli = new CliInput(['src']);

        $this->assertSame(['src'], $cli->directories());
        $this->assertSame([], $cli->excludePaths());
        $this->assertSame(['*.php'], $cli->filenames());
    }

    public function testExcludeDir()
    {
        $cli = new CliInput(['src'], '  vendor ');

        $this->assertSame(['src'], $cli->directories());
        $this->assertSame(['vendor'], $cli->excludePaths());
        $this->assertSame(['*.php'], $cli->filenames());
    }

    public function testExcludeMultipleDir()
    {
        $cli = new CliInput(['src'], ' vendor  ,  tests  ');

        $this->assertSame(['src'], $cli->directories());
        $this->assertSame(['vendor', 'tests'], $cli->excludePaths());
        $this->assertSame(['*.php'], $cli->filenames());
    }

    public function testExtension()
    {
        $cli = new CliInput(['src'], null, ' inc ');

        $this->assertSame(['src'], $cli->directories());
        $this->assertSame([], $cli->excludePaths());
        $this->assertSame(['*.inc'], $cli->filenames());
    }

    public function testMultipleExtension()
    {
        $cli = new CliInput(['src'], null, '  php  , inc ');

        $this->assertSame(['src'], $cli->directories());
        $this->assertSame([], $cli->excludePaths());
        $this->assertSame(['*.php', '*.inc'], $cli->filenames());
    }

    public function testExtensionWithAsterisk()
    {
        $cli = new CliInput(['src'], null, '*.inc');

        $this->assertSame(['src'], $cli->directories());
        $this->assertSame([], $cli->excludePaths());
        $this->assertSame(['*.inc'], $cli->filenames());
    }
}
