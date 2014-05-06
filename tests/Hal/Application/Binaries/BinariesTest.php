<?php
namespace Test\Hal\Binaries;

use Hal\Metrics\Complexity\Text\Halstead\Halstead;
use Hal\Metrics\Complexity\Text\Halstead\Result;

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

        $command = sprintf('php '.__DIR__.'/../../../../build/metrics.phar '.$this->toExplore);
        $output = shell_exec($command);

        $this->assertRegExp('/Maintenability/', $output);
    }

    public function testICanRunIsolatedPhar() {

        $path = getcwd();
        copy(__DIR__.'/../../../../build/metrics.phar', sys_get_temp_dir().'/metrics.phar');
        chdir(sys_get_temp_dir());
        $command = sprintf('php '.sys_get_temp_dir().'/metrics.phar  '.$this->toExplore);
        $output = shell_exec($command);
        chdir($path);

        $this->assertRegExp('/Maintenability/', $output);
    }

    public function testICanRunPharWithHtmlFormater() {

        $to = sys_get_temp_dir().'/tmpunit.html';
        $path = getcwd();
        copy(__DIR__.'/../../../../build/metrics.phar', sys_get_temp_dir().'/metrics.phar');
        chdir(sys_get_temp_dir());

        $command = sprintf('php '.sys_get_temp_dir().'/metrics.phar  --report-html='.$to.' '.$this->toExplore);
        $output = shell_exec($command);
        chdir($path);

        $this->assertFileExists($to);
        $content = file_get_contents($to);
        $this->assertRegExp('<html>', $content);
        $this->assertRegExp('<body>', $content);
    }

    public function testICanRunPharWithXmlFormater() {

        $to = sys_get_temp_dir().'/tmpunit.xml';
        $path = getcwd();
        copy(__DIR__.'/../../../../build/metrics.phar', sys_get_temp_dir().'/metrics.phar');
        chdir(sys_get_temp_dir());

        $command = sprintf('php '.sys_get_temp_dir().'/metrics.phar  --report-xml='.$to.' '.$this->toExplore);
        $output = shell_exec($command);
        chdir($path);

        $this->assertFileExists($to);
        $content = file_get_contents($to);
        $this->assertRegExp('<modules>', $content);
        $this->assertRegExp('<project>', $content);
    }

    public function testICanRunPhpFile() {

        $command = sprintf('php '.__DIR__.'/../../../../bin/metrics.php   '.$this->toExplore);
        $output = shell_exec($command);

        $this->assertRegExp('/Maintenability/', $output);
    }

}