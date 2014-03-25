<?php
namespace Test\Hal\Halstead;

use Hal\Loc\Result;
use Hal\Loc\Loc;

/**
 * @group loc
 * @group metric
 */
class LocTest extends \PHPUnit_Framework_TestCase {

    public function testICanCountLoc() {

        $filename = __DIR__.'/../../resources/loc/f1.php';
        $loc = new Loc(new \Hal\Token\Tokenizer());
        $r = $loc->calculate($filename);
        $this->assertEquals(14, $r->getCommentLoc());
        $this->assertEquals(33, $r->getLoc());
        $this->assertEquals(2, $r->getLogicalLoc());

    }

    public function testLocResultCanBeConvertedToArray() {

        $result = new \Hal\Loc\Result();
        $array = $result->asArray();

        $this->assertArrayHasKey('loc', $array);
        $this->assertArrayHasKey('logicalLoc', $array);
    }
}