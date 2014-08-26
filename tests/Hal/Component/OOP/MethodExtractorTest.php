<?php
namespace Test\Hal\Component\OOP;

use Hal\Component\Token\TokenCollection;
use Hal\Metrics\Design\Component\MaintenabilityIndex\MaintenabilityIndex;
use Hal\Metrics\Design\Component\MaintenabilityIndex\Result;
use Hal\Component\OOP\Extractor\Extractor;
use Hal\Component\OOP\Extractor\MethodExtractor;
use Hal\Component\OOP\Extractor\Searcher;

/**
 * @group oop
 * @group extractor
 */
class MethodExtractorTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider providesForMethodsArgs
     */
    public function testMethodsAreFound($filename, $expectedMethods) {

        $extractor = new Extractor(new \Hal\Component\Token\Tokenizer());
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
        $extractor = new Extractor(new \Hal\Component\Token\Tokenizer());
        $result = $extractor->extract(__DIR__.'/../../../resources/oop/f6.php');
        $classes = $result->getClasses();
        $class = $classes[0];
        $methods = $class->getMethods();
        $method = $methods['foo'];
        $expected = <<<EOT
\$a = strtoupper((string)\$a);
return \$a;
EOT;

        $this->assertEquals($expected, $method->getContent());

        $methods = $class->getMethods();
        $method = end($methods);
        $expected = <<<EOT
\$x = 1 * 2;
die();
EOT;
        $this->assertEquals($expected, $method->getContent());
    }

    public function providesForMethodsArgs() {
        return array(
            array(__DIR__.'/../../../resources/oop/f1.php', array())
            , array(__DIR__.'/../../../resources/oop/f2.php', array(
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


    /**
     * @dataProvider provideCodeForReturns
     */
    public function testReturnsAreFound($expected, $code) {
        $searcher = new Searcher();
        $methodExtractor = new MethodExtractor($searcher);

        $tokens = new TokenCollection(token_get_all($code));
        $n = 1;
        $method = $methodExtractor->extract($n, $tokens);
        $this->assertEquals($expected, sizeof($method->getReturns(), COUNT_NORMAL));
    }

    public function provideCodeForReturns() {
        return array(
            array(1, '<?php public function foo() { return 1; }')
            , array(0, '<?php public function foo() { }')
            , array(2, '<?php public function foo() { if(true) { return 1; } return 2; }')
            , array(0, '<?php public function bar() { $x->a();  }')
        );
    }

    /**
     * @dataProvider provideCodeForNew
     * @group wip
     */
    public function testConstructorAreFound($expected, $code) {
        $searcher = new Searcher();
        $methodExtractor = new MethodExtractor($searcher);

        $tokens = new TokenCollection(token_get_all($code));
        $n = 1;
        $method = $methodExtractor->extract($n, $tokens);

        $this->assertEquals($expected, $method->getDependencies());
    }

    public function provideCodeForNew() {
        return array(
            array(array(), '<?php public function foo() { return 1; }')
            , array(array('A'), '<?php public function bar() { new A();  }')
            , array(array('A'), '<?php public function bar() { new A(1,2,3);  }')
            , array(array(), '<?php public function bar($d = false) {  }')
            , array(array(), '<?php public function bar($d = false) {  }')
        );
    }
}