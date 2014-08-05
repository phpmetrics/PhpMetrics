<?php
namespace Test\Hal\Component\Config;
use Hal\Application\Config\TreeBuilder;
use Hal\Component\Config\Hydrator;
use Hal\Component\Config\Loader;
use Hal\Component\Config\Validator;


/**
 * @group config
 */
class LoaderTest extends \PHPUnit_Framework_TestCase {

    public function testICanDefineRulesInConfigFile() {

        $content = <<<EOT
default:
    rules:
        example1: [ 1,2,3 ]
EOT;
        $filename = \tempnam(sys_get_temp_dir(), 'rule.yml');
        file_put_contents($filename, $content);
        $treeBuilder = new TreeBuilder();
        $hydrator = new Hydrator(new Validator($treeBuilder->getTree()));
        $loader = new Loader($hydrator);
        $config = $loader->load($filename);
        unlink($filename);


        $this->assertInstanceOf('\Hal\Application\Config\Configuration', $config);
        $this->assertEquals(array(1,2,3), $config->getRuleSet()->getRule('example1'));

    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage configuration file is not accessible
     */
    public function testExplicitExceptionIsThrownWhenConfigFileIsNotFound() {
        $treeBuilder = new TreeBuilder();
        $hydrator = new Hydrator(new Validator($treeBuilder->getTree()));
        $loader = new Loader($hydrator);
        $config = $loader->load('/pat/to/anything');
    }

}