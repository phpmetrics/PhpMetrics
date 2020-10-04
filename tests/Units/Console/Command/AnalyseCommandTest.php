<?php declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Phrozer\Console\CliInput;
use Phrozer\DiFactory;
use Phrozer\ExitInterface;
use Phrozer\Phrozer;
use Phrozer\Runner\TaskExecutor;

/**
 * @covers Phrozer\Phrozer
 * @covers Phrozer\DiFactory
 * @covers Phrozer\Console\Command\AnalyseCommand
 *
 * @uses Phrozer\Console\CliInput
 * @uses Phrozer\FileLoader
 */
final class AnalyseCommandTest extends TestCase
{

    /** @var Phrozer */
    private $app;

    /** @var MockObject|TaskExecutor */
    private $mock;

    /** @return void */
    protected function setUp()
    {
        parent::setUp();

        $this->mock = $this->createMock(TaskExecutor::class);
        $rules[TaskExecutor::class] = ['instanceOf' => $this->mock];

        $exitMock = $this->createMock(ExitInterface::class);
        $this->app = new Phrozer(DiFactory::container($rules), $exitMock, '');
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
