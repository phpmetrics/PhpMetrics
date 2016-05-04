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

        $code = <<<EOT

/*
sfdsg
fdg
fdsg
*/




// another
echo 'ok'; // no
/* ici */


EOT;

        $loc = new Loc();
        $r = $loc->calculate($code);
        $this->assertEquals(14, $r->getLoc());
        $this->assertEquals(7, $r->getCommentLoc());
        $this->assertEquals(1, $r->getLogicalLoc());

    }

    public function testLocResultCanBeConvertedToArray() {

        $result = new \Hal\Metrics\Complexity\Text\Length\Result();
        $array = $result->asArray();

        $this->assertArrayHasKey('loc', $array);
        $this->assertArrayHasKey('logicalLoc', $array);
    }
}
