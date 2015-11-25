<?php
namespace Test\Hal\Binaries;

use Hal\Metrics\Complexity\Text\Halstead\Halstead;
use Hal\Metrics\Complexity\Text\Halstead\Result;

/**
 * @group binary
 * @group failure
 */
class FailureConditionTest extends \PHPUnit_Framework_TestCase {

    private $toExplore;

    public function setup() {
        $this->toExplore = sys_get_temp_dir().'/metrics-tmp';
        if(!file_exists($this->toExplore)) {
            mkdir($this->toExplore);
            file_put_contents($this->toExplore.'/tmp.php', "<?php \n echo 'ok';\n echo 'ok';");
        }
    }

    public function teardown() {
        unlink($this->toExplore.'/tmp.php');
        rmdir($this->toExplore);
        unset($this->toExplore);
    }

    public function testStatusIsCodeIsZeroIfNotConditionOfFailureIsGiven() {

        $command = sprintf('php '.__DIR__.'/../../../../build/phpmetrics.phar '.$this->toExplore);
        exec($command, $output, $status);
        $this->assertEquals(0, $status);
    }

    public function testStatusIsOneIfConditionOfFailureMatchs() {

        $command = sprintf('php '.__DIR__.'/../../../../build/phpmetrics.phar --failure-condition="sum.loc < 10" '.$this->toExplore);
        exec($command, $output, $status);
        $this->assertEquals(1, $status);
    }


}