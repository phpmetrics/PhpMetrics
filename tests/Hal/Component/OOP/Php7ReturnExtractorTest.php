<?php
namespace Test\Hal\Component\OOP;

use Hal\Component\OOP\Reflected\ReflectedReturn;
use Hal\Component\Token\TokenCollection;
use Hal\Metrics\Design\Component\MaintainabilityIndex\MaintainabilityIndex;
use Hal\Metrics\Design\Component\MaintainabilityIndex\Result;
use Hal\Component\OOP\Extractor\Extractor;
use Hal\Component\OOP\Extractor\MethodExtractor;
use Hal\Component\OOP\Extractor\Searcher;

/**
 * @group oop
 * @group extractor
 * @group php7
 */
class Php7ReturnExtractorTest extends \PHPUnit_Framework_TestCase {

    public function testAnonymousClassIsFound() {

        $filename = __DIR__.'/../../../resources/oop/php7-return1.php';
        $extractor = new Extractor(new \Hal\Component\Token\Tokenizer());
        $result = $extractor->extract($filename);

        $classes = $result->getClasses();
        $this->assertEquals(1, sizeof($classes));
        $methods = $classes[0]->getMethods();
        $this->assertEquals(1, sizeof($methods));
        $method = $methods['foo'];

        $expected = new ReflectedReturn();
        $expected
            ->setMode(ReflectedReturn::STRICT_TYPE_HINT)
            ->setType('array')
            ;

        $this->assertEquals(array($expected), $method->getReturns());
    }

    public function testAnonymousClassIsFoundAndNamespaced() {

        $filename = __DIR__.'/../../../resources/oop/php7-return2.php';
        $extractor = new Extractor(new \Hal\Component\Token\Tokenizer());
        $result = $extractor->extract($filename);

        $classes = $result->getClasses();
        $this->assertEquals(2, sizeof($classes));
        $methods = $classes[0]->getMethods();
        $this->assertEquals(1, sizeof($methods));
        $method = $methods['foo'];

        $expected = new ReflectedReturn();
        $expected
            ->setMode(ReflectedReturn::STRICT_TYPE_HINT)
            ->setType('\\My\\Class2')
        ;

        $this->assertEquals(array($expected), $method->getReturns());
    }

}