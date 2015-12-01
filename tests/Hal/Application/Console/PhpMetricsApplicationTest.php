<?php
namespace Test\Hal\Application\Console;
use Hal\Application\Console\PhpMetricsApplication;
use Symfony\Component\Console\Input\InputOption;


/**
 * @group console
 */
class PhpMetricsApplicationTest extends \PHPUnit_Framework_TestCase {

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

    public function testConsoleRunsByDefaultMetricsCommand() {
        $app = new PhpMetricsApplication();
        $command = $app->get('metrics');
        $this->assertInstanceOf('\Hal\Application\Command\RunMetricsCommand', $command);
    }

    public function testApplicationCanBeRunnedWithoutName() {
        $command = sprintf('php '.__DIR__.'/../../../../bin/phpmetrics '.$this->toExplore);
        $output = shell_exec($command);
        $this->assertRegExp('/Maintainability/', $output);
    }

    public function testApplicationCanBeRun() {
        $app = new PhpMetricsApplication();
        $app->setAutoExit(false);

        $input = $this->getMock('\Symfony\Component\Console\Input\InputInterface');
        $input->expects($this->any())->method('hasParameterOption')->will($this->returnValue(true));
        $code = $app->run($input);

        $this->assertEquals(0, $code);
    }
}