<?php
namespace Test\Hal\Application\Command;
use Hal\Application\Command\InitConfigCommand;
use Hal\Application\Command\RunMetricsCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Tester\CommandTester;


/**
 * @group command
 * @group console
 */
class InitConfigCommandTest extends \PHPUnit_Framework_TestCase {



    /**
     * @outputBuffering disabled
     */
    public function testICanExecuteCommand() {
        $command = new InitConfigCommand();

        $workdir = sys_get_temp_dir();
        chdir($workdir);
        $required = $workdir.'/.phpmetrics.yml';

        $commandTester = new CommandTester($command);
        $commandTester->execute(array());
        $this->assertTrue(file_exists($required));

        unlink($required);
    }
}