<?php
namespace Test\Hal\OOP;

use Hal\MaintenabilityIndex\MaintenabilityIndex;
use Hal\MaintenabilityIndex\Result;
use Hal\OOP\Extractor\Extractor;

/**
 * @group oop
 */
class OOPExtractorTest extends \PHPUnit_Framework_TestCase {


    /**
     * @dataProvider providesForClassnames
     */
    public function testClassnameIsFound($filename, $expected) {

        $result = new \Hal\OOP\Extractor\Result();
        $extractor = new Extractor($result);
        $result = $extractor->extract($filename);

        $this->assertCount(sizeof($expected), $result->getClasses());
        foreach($result->getClasses() as $index => $class) {
            $this->assertEquals($expected[$index], $class->getFullname());
        }

    }

    public function providesForClassnames() {
        return array(
            array(__DIR__.'/../../resources/oop/f1.php', array('\Titi'))
            , array(__DIR__.'/../../resources/oop/f2.php', array('\My\Example\Titi'))
            , array(__DIR__.'/../../resources/oop/f3.php', array('\My\Example\Titi1', '\My\Example\Titi2'))
        );
    }


    /**
     * @dataProvider providesForMethods
     * @group wip
     */
    public function testMethodsAreFound($filename, $expectedMethods) {

        $result = new \Hal\OOP\Extractor\Result();
        $extractor = new Extractor($result);
        $result = $extractor->extract($filename);

        foreach($result->getClasses() as $index => $class) {

            $this->assertCount(sizeof($expectedMethods), $class->getMethods());

            foreach($class->getMethods() as $method) {
                $found = false;
                foreach($expectedMethods as $expectedMethod) {

                    list($methodName, $args) = $expectedMethod;

                    if($methodName == $method->getName()) {
                        $found = true;

                        $this->assertCount(sizeof($args), $method->getArguments(), sprintf('all arguments of "%s()" found', $method->getName()));

                        foreach($method->getArguments() as $pos => $argument) {
                            list($varname, $type, $required) = $args[$pos];

                            $this->assertEquals($varname, $argument->getName(), 'argument name found');
                            $this->assertEquals($type, $argument->getType(), 'argument type found');
                            $this->assertEquals($required, $argument->isRequired(), 'argument is required found');
                        }

                    }
                }

                if(!$found) {
                    throw new \Exception(sprintf('method "%s" is found but wan not expected', $method->getName()));
                }

            }

        }

    }

    public function testDependenciesAreGivenWithoutAlias() {

        $file = __DIR__.'/../../resources/oop/f4.php';
        $result = new \Hal\OOP\Extractor\Result();
        $extractor = new Extractor($result);
        $result = $extractor->extract($file);

        $classes = $result->getClasses();
        $this->assertCount(1, $classes, 'all classes are found');

        $class = $classes[0];
        $dependencies = $class->getDependencies();

        $expected = array('\Full\AliasedClass', 'Toto');

        $this->assertEquals($expected, $dependencies, 'alias found');

    }

    public function providesForMethods() {
        return array(
            array(__DIR__.'/../../resources/oop/f1.php', array())
            , array(__DIR__.'/../../resources/oop/f2.php', array(
                // method
                array('foo', array(
                    // args
                ))
                // method
                , array('bar', array(
                    // args
                    array('$c', 'AnotherClass', false)
                ))
                // method
                , array('baz', array(
                    // args
                    array('$c', '\Namespaced\AnotherClass', true)
                    , array('$c2', 'AnotherClass', false)
                ))
              )
            )

        );
    }
}