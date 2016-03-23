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
class Php7ScalarTypeHintTest extends \PHPUnit_Framework_TestCase {

    public function testAnonymousClassIsFound() {

        $filename = __DIR__.'/../../../resources/oop/php7-scalarhint1.php';
        $tokens = (new \Hal\Component\Token\Tokenizer())->tokenize($filename);
        $extractor = new Extractor();
        $result = $extractor->extract($tokens);

        $classes = $result->getClasses();
        $this->assertEquals(1, sizeof($classes));
        $main = $classes[0];

        $this->assertEquals(0, sizeof($main->getDependencies()));
    }

}
