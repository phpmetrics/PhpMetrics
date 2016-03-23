<?php
namespace Test\Hal\Metrics\Complexity\Text\Halstead;

use Hal\Metrics\Complexity\Text\Length\Result;
use Hal\Metrics\Complexity\Text\Length\Loc;

/**
 * @group loc
 * @group metric
 */
class LocTest extends \PHPUnit_Framework_TestCase {

    public function testICanCountLoc() {

        $filename = __DIR__.'/../../../../../resources/loc/f1.php';
        $tokens = (new \Hal\Component\Token\Tokenizer())->tokenize($filename);
        $loc = new Loc();
        $r = $loc->calculate($filename, $tokens);
        $this->assertEquals(14, $r->getCommentLoc());
        $this->assertEquals(33, $r->getLoc());
        $this->assertEquals(2, $r->getLogicalLoc());

    }

    public function testLocResultCanBeConvertedToArray() {

        $result = new \Hal\Metrics\Complexity\Text\Length\Result();
        $array = $result->asArray();

        $this->assertArrayHasKey('loc', $array);
        $this->assertArrayHasKey('logicalLoc', $array);
    }
}
