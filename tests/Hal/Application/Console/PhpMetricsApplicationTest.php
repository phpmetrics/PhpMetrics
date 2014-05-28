<?php
namespace Test\Hal\Application\Console;
use Hal\Application\Console\PhpMetricsApplication;
use Symfony\Component\Console\Input\InputOption;


/**
 * @group console
 */
class PhpMetricsApplicationTest extends \PHPUnit_Framework_TestCase {

    public function testConsoleRunsByDefaultMetricsCommand() {
        $app = new PhpMetricsApplication();
        $command = $app->get('metrics');
        $this->assertInstanceOf('\Hal\Application\Command\RunMetricsCommand', $command);
    }

    public function testApplicationCanBeRunnedWithoutName() {
        $app = new PhpMetricsApplication();
        $df = $app->getDefinition();

        $this->assertEquals(0, $df->getArgumentCount());
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