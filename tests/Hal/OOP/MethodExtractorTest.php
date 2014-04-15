<?php
namespace Test\Hal\OOP;

use Hal\MaintenabilityIndex\MaintenabilityIndex;
use Hal\MaintenabilityIndex\Result;
use Hal\OOP\Extractor\Extractor;
use Hal\OOP\Extractor\MethodExtractor;
use Hal\OOP\Extractor\Searcher;

/**
 * @group oop
 */
class MethodExtractorTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider providesForMethodsArgs
     */
    public function testMethodsAreFound($filename, $expectedMethods) {

        $extractor = new Extractor(new \Hal\Token\Tokenizer());
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


    public function testContentOfMethodIsFound() {
        $extractor = new Extractor(new \Hal\Token\Tokenizer());
        $result = $extractor->extract(__DIR__.'/../../resources/oop/f6.php');
        $class = reset($result->getClasses());
        $method = reset($class->getMethods());
        $expected = <<<EOT
\$a = strtoupper((string)\$a);
return \$a;
EOT;

        $this->assertEquals($expected, $method->getContent());


        $method = end($class->getMethods());
        $expected = <<<EOT
\$x = 1 * 2;
die();
EOT;
        $this->assertEquals($expected, $method->getContent());
    }

    public function providesForMethodsArgs() {
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