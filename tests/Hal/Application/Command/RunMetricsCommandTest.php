<?php
namespace Test\Hal\Application\Command;
use Hal\Application\Command\RunMetricsCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Tester\CommandTester;


/**
 * @group command
 * @group console
 */
class RunMetricsCommandTest extends \PHPUnit_Framework_TestCase {

    private $toExplore;

    public function setup() {
        $this->toExplore = sys_get_temp_dir().'/metrics-tmp';
        if(!file_exists($this->toExplore)) {
            mkdir($this->toExplore);
            file_put_contents($this->toExplore.'/tmp.php', "<?php echo 'ok';");
        }
    }

    public function teardown() {
        unlink($this->toExplore.'/tmp.php');
        rmdir($this->toExplore);
        unset($this->toExplore);
    }

    public function testICanExecuteCommand() {
        $command = new RunMetricsCommand();

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'path'    => $this->toExplore
            )
        );

        $this->assertRegExp('/PHPMetrics by Jean-François Lépine/', $commandTester->getDisplay());
    }
}