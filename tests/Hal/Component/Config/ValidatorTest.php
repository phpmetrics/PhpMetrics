<?php
namespace Test\Hal\Component\Config;
use Hal\Application\Config\TreeBuilder;
use Hal\Component\Config\Validator;


/**
 * @group config
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase {

   public function testICanValidateConfig() {
       $config = array(
           'phpmetrics' => array(
               'rules' => array(
                   'rule1' => array(1,2,3)
               )
           )
       );

       $treeBuilder = new TreeBuilder();
       $validator = new Validator($treeBuilder->getTree());
       $result = $validator->validates($config);

       $this->assertArrayHasKey('rules', $result);
   }


    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testInvalideConfigurationAreDetected() {
       $config = array(
           'phpmetrics' => array(
               'rules' => array(
                   'rule1' => array(1,2,3, 4)
               )
           )
       );

        $treeBuilder = new TreeBuilder();
        $validator = new Validator($treeBuilder->getTree());
        $validator->validates($config);
   }
}