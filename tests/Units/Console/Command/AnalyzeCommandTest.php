<?php declare(strict_types=1);

use Phpmetrix\CliApplication;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Phpmetrix\Console\CliInput;
use Phpmetrix\Console\Command\AnalyzeCommand;
use Phpmetrix\DiFactory;
use Phpmetrix\ExitInterface;
use Phpmetrix\Runner\TaskExecutor;

/**
 * @covers Phpmetrix\CliApplication
 * @covers Phpmetrix\DiFactory
 * @covers Phpmetrix\Console\Command\AnalyzeCommand
 *
 * @uses Phpmetrix\Console\CliInput
 * @uses Phpmetrix\FileLoader
 */
final class AnalyzeCommandTest extends TestCase
{

    /** @var CliApplication */
    private $app;

    /** @var MockObject|TaskExecutor */
    private $mock;

    /** @return void */
    protected function setUp()
    {
        parent::setUp();

        $this->mock = $this->createMock(TaskExecutor::class);
        $exitMock = $this->createMock(ExitInterface::class);

        $rules['$analyser'] = ['instanceOf' => $this->mock];
        $rules[AnalyzeCommand::class] = ['substitutions' => [TaskExecutor::class => '$analyser']];

        $this->app = new CliApplication(DiFactory::container($rules), $exitMock, '');
    }

    public function testPassOnlyOneDirectory()
    {
        $expected = new CliInput(['src']);
        $this->mock->expects($this->once())->method('process')->with($this->equalTo($expected));

        $args = ['appname', 'analyse', 'src'];
        $this->app->handle($args);
    }

    public function testPassMultipleDirectories()
    {
        $expected = new CliInput(['src', 'bin', 'test']);
        $this->mock->expects($this->once())->method('process')->with($this->equalTo($expected));

        $args = ['appname', 'analyse', 'src', 'bin', 'test'];
        $this->app->handle($args);
    }

    public function testExcludeDirectories()
    {
        $expected = new CliInput(['src'], 'vendor,tests');
        $this->mock->expects($this->once())->method('process')->with($this->equalTo($expected));

        $args = ['appname', 'analyse', 'src', '--exclude', 'vendor,tests'];
        $this->app->handle($args);
    }

    public function testExcludeWithEqual()
    {
        $expected = new CliInput(['src'], 'vendor,tests');
        $this->mock->expects($this->once())->method('process')->with($this->equalTo($expected));

        $args = ['appname', 'analyse', 'src', '--exclude=vendor,tests'];
        $this->app->handle($args);
    }
}
