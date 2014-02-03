<?php
namespace Test\Hal\File;
use Hal\File\Finder;


/**
 * @group bounds
 */
class FinderTest extends \PHPUnit_Framework_TestCase {

    private $toExplore;

    public function setup() {
        $this->toExplore = sys_get_temp_dir().'/metrics-tmp-finder';
        if(!file_exists($this->toExplore)) {
            mkdir($this->toExplore);
            file_put_contents($this->toExplore.'/tmp.php', "<?php echo 'ok';");
            file_put_contents($this->toExplore.'/tmp2.php', "<?php echo 'ok';");
            file_put_contents($this->toExplore.'/tmp3.php', "<?php echo 'ok';");
            file_put_contents($this->toExplore.'/tmp4.txt', "<?php echo 'ok';");
        }
    }

    public function testICanFindFilesByExtension() {
        $finder = new Finder('txt');
        $results = $finder->find($this->toExplore);
        $this->assertEquals(1, sizeof($results));
    }

    public function testIFindPhpFilesByDefault() {
        $finder = new Finder();
        $results = $finder->find($this->toExplore);
        $this->assertEquals(3, sizeof($results));
    }
}