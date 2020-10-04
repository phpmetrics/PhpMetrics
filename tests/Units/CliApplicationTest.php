<?php declare(strict_types=1);

use Phpmetrix\CliApplication;
use Phpmetrix\Console\CliInput;
use Phpmetrix\Console\Command\AnalyzeCommand;
use Phpmetrix\DiFactory;
use Phpmetrix\ExitInterface;
use Phpmetrix\Runner\TaskExecutor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers Phpmetrix\CliApplication
 * @covers Phpmetrix\DiFactory
 *
 * @uses Phpmetrix\Console\CliInput
 * @uses Phpmetrix\Console\Command\AnalyzeCommand
 */
final class CliApplicationTest extends TestCase
{

    /** @var CliApplication */
    private $app;

    /** @var MockObject|TaskExecutor */
    private $mock;

    /** @return void */
    protected function setUp()
    {
        $this->mock = $this->createMock(TaskExecutor::class);
        $exitMock = $this->createMock(ExitInterface::class);

        $rules['$analyser'] = ['instanceOf' => $this->mock];
        $rules[AnalyzeCommand::class] = ['substitutions' => [TaskExecutor::class => '$analyser']];

        $this->app = new CliApplication(DiFactory::container($rules), $exitMock, '');
    }

    public function testRunning()
    {
        $expected = new CliInput(['src']);
        $this->mock->expects($this->once())->method('process')->with($this->equalTo($expected));

        $this->app->handle(['appname', 'analyse', 'src']);
    }
}
