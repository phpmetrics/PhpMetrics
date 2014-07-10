<?php
namespace Test\Hal\Component\Config;
use Hal\Component\Config\Loader;


/**
 * @group config
 */
class LoaderTest extends \PHPUnit_Framework_TestCase {

    public function testICanDefineRulesInConfigFile() {

        $content = <<<EOT
rules:
    example1: [ 1,2,3 ]
EOT;
        $filename = \tempnam(sys_get_temp_dir(), 'rule.yml');
        file_put_contents($filename, $content);
        $loader = new Loader();
        $config = $loader->load($filename);
        unlink($filename);


        $this->assertInstanceOf('\Hal\Component\Config\Configuration', $config);
        $this->assertEquals(array(1,2,3), $config->getRuleSet()->getRule('example1'));

    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage configuration file is not accessible
     */
    public function testExplicitExceptionIsThrownWhenConfigFileIsNotFound() {
        $loader = new Loader();
        $config = $loader->load('/pat/to/anything');
    }

}