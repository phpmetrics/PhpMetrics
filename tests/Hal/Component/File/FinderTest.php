<?php
namespace Test\Hal\Component\File;
use Hal\Component\File\Finder;


/**
 * @group bounds
 */
class FinderTest extends \PHPUnit_Framework_TestCase {

    private $toExplore;

    protected function setup() {
        $this->toExplore = sys_get_temp_dir().'/metrics-tmp-finder';
        if(!file_exists($this->toExplore)) {
            mkdir($this->toExplore);
            mkdir($this->toExplore . '/toExclude');
            file_put_contents($this->toExplore.'/tmp.php', "<?php echo 'ok';");
            file_put_contents($this->toExplore.'/tmp2.php', "<?php echo 'ok';");
            file_put_contents($this->toExplore.'/tmp3.php', "<?php echo 'ok';");
            file_put_contents($this->toExplore.'/tmp4.txt', "<?php echo 'ok';");
            file_put_contents($this->toExplore.'/toExclude/tmp.php', "<?php echo 'test';");
        }
    }

    protected function tearDown()
    {
        unlink($this->toExplore.'/tmp.php');
        unlink($this->toExplore.'/tmp2.php');
        unlink($this->toExplore.'/tmp3.php');
        unlink($this->toExplore.'/tmp4.txt');
        unlink($this->toExplore.'/toExclude/tmp.php');
        rmdir($this->toExplore.'/toExclude');
        rmdir($this->toExplore);
    }

    public function testICanFindFilesByExtension() {
        $finder = new Finder('txt');
        $results = $finder->find($this->toExplore);
        $this->assertEquals(1, sizeof($results));
    }

    public function testICanGiveFilepathInsteadOfDirectory() {
        $finder = new Finder('txt');
        $results = $finder->find($this->toExplore.'/tmp.php');
        $this->assertEquals(1, sizeof($results));
    }

    public function testIFindPhpFilesByDefault() {
        $finder = new Finder();
        $results = $finder->find($this->toExplore);
        $this->assertEquals(4, sizeof($results));
    }

    public function testIDontFindFilesFromExcludedDirs()
    {
        $finder = new Finder('php', 'toExclude');
        $results = $finder->find($this->toExplore);
        $this->assertEquals(3, sizeof($results));
    }
}
