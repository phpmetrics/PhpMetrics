<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Phpmetrix\Console\Command\AnalyzeCommand;
use Phpmetrix\Runner\TaskExecutor;

/**
 * @covers \Phpmetrix\Console\Command\AnalyzeCommand
 */
final class AnalyzeCommandTest extends TestCase
{

    /** @var AnalyzeCommand */
    private $command;

    /** @return void */
    protected function setUp()
    {
        parent::setUp();

        $mock = $this->createMock(TaskExecutor::class);
        $this->command = new AnalyzeCommand($mock);
    }

    public function testPassOnlyOneDirectory()
    {
        $args = ['analyse', 'src'];
        $this->command->parse($args);

        $this->assertSame('src', $this->command->dir);
        $this->assertSame([], $this->command->dirs);
        $this->assertSame(null, $this->command->exclude);
        $this->assertSame(null, $this->command->ext);
    }

    public function testPassMultipleDirectories()
    {
        $args = ['analyse', 'src', 'bin', 'test'];
        $this->command->parse($args);

        $this->assertSame('src', $this->command->dir);
        $this->assertSame(['bin', 'test'], $this->command->dirs);
    }

    public function testExcludeDirectories()
    {
        $args = ['analyse', 'src', '--exclude', 'vendor,tests'];
        $this->command->parse($args);

        $this->assertSame('vendor,tests', $this->command->exclude);
    }

    public function testExcludeWithEqual()
    {
        $args = ['analyse', 'src', '-e=vendor,tests'];
        $this->command->parse($args);

        $this->assertSame('vendor,tests', $this->command->exclude);
    }

    public function testPassExtension()
    {
        $args = ['analyse', 'src', '--ext=php,inc'];
        $this->command->parse($args);

        $this->assertSame('php,inc', $this->command->ext);
    }
}
