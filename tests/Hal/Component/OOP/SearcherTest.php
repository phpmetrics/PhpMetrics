<?php
namespace Test\Hal\Component\OOP;

use Hal\Component\OOP\Extractor\Searcher;
use Hal\Component\OOP\Reflected\ReflectedInterface;
use Hal\Component\Token\TokenCollection;
use Hal\Component\Token\Tokenizer;
use Hal\Metrics\Design\Component\MaintainabilityIndex\MaintainabilityIndex;
use Hal\Metrics\Design\Component\MaintainabilityIndex\Result;
use Hal\Component\OOP\Extractor\Extractor;

/**
 * @group oop
 */
class SearcherTest extends \PHPUnit_Framework_TestCase {


    public function testPreviousIsFound() {
        $code = '<?php class A { public function foo(){} }';
        $tokens = new TokenCollection(token_get_all($code));
        $end = sizeof($tokens) - 1;

        $searcher = new Searcher();
        $position = $searcher->getPositionOfPrevious(T_PUBLIC, $end, $tokens);
        $this->assertEquals(7, $position);
    }

    public function testIsPrecededWorks() {
        $code = '<?php class A { public function foo(){} }';
        $tokens = new TokenCollection(token_get_all($code));
        $positionOfMethod = 9;

        $searcher = new Searcher();
        $this->assertTrue($searcher->isPrecededBy(T_PUBLIC, $positionOfMethod, $tokens));
        $this->assertFalse($searcher->isPrecededBy(T_PRIVATE, $positionOfMethod, $tokens));
    }

    public function testNextIsFound() {
        $code = '<?php class A { public function foo(){} }';
        $tokens = new TokenCollection(token_get_all($code));
        $searcher = new Searcher();
        $position = $searcher->getPositionOfNext(T_PUBLIC, 1, $tokens);
        $this->assertEquals(7, $position);
    }

    public function testIsFollowedWorks() {
        $code = '<?php class A { public function foo(){} }';
        $tokens = new TokenCollection(token_get_all($code));
        $searcher = new Searcher();
        $this->assertTrue($searcher->isFollowedBy(T_FUNCTION, 7, $tokens));
    }

}