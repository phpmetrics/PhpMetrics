<?php
namespace Test\Hal\Binaries;

use Hal\Halstead\Halstead;
use Hal\Halstead\Result;

/**
 * @group binary
 */
class BinariesTest extends \PHPUnit_Framework_TestCase {

    public function testICanRunPhar() {

        $command = sprintf('php '.__DIR__.'/../../../build/metrics.phar');
        $output = shell_exec($command);

        $this->assertRegExp('/Delivred Bugs/', $output);
    }

    public function testICanRunPhpFile() {

        $command = sprintf('php '.__DIR__.'/../../../bin/metrics.php');
        $output = shell_exec($command);

        $this->assertRegExp('/Delivred Bugs/', $output);
    }

}