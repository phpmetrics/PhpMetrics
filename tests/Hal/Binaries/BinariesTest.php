<?php
namespace Test\Hal\Binaries;

use Hal\Halstead\Halstead;
use Hal\Halstead\Result;

/**
 * @group binary
 */
class BinariesTest extends \PHPUnit_Framework_TestCase {

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

    public function testICanRunPhar() {

        $command = sprintf('php '.__DIR__.'/../../../build/metrics.phar --report=details '.$this->toExplore);
        $output = shell_exec($command);

        $this->assertRegExp('/Delivred Bugs/', $output);
    }

    public function testICanRunIsolatedPhar() {

        $path = getcwd();
        copy(__DIR__.'/../../../build/metrics.phar', sys_get_temp_dir().'/metrics.phar');
        chdir(sys_get_temp_dir());
        $command = sprintf('php '.sys_get_temp_dir().'/metrics.phar  --report=details  '.$this->toExplore);
        $output = shell_exec($command);
        chdir($path);

        $this->assertRegExp('/Delivred Bugs/', $output);
    }

    public function testICanRunPharWithHtmlFormater() {

        $path = getcwd();
        copy(__DIR__.'/../../../build/metrics.phar', sys_get_temp_dir().'/metrics.phar');
        chdir(sys_get_temp_dir());

        $command = sprintf('php '.sys_get_temp_dir().'/metrics.phar  --report=details --format=html '.$this->toExplore);
        $output = shell_exec($command);
        chdir($path);

        $this->assertRegExp('<html>', $output);
        $this->assertRegExp('<body>', $output);
    }

    public function testICanRunPhpFile() {

        $command = sprintf('php '.__DIR__.'/../../../bin/metrics.php  --report=details '.$this->toExplore);
        $output = shell_exec($command);

        $this->assertRegExp('/Delivred Bugs/', $output);
    }

}