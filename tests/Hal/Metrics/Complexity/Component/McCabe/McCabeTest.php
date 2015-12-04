<?php
namespace Test\Hal\Metrics\Complexity\Text\Halstead;

use Hal\Metrics\Complexity\Component\McCabe\McCabe;
use Hal\Metrics\Complexity\Component\McCabe\Result;

/**
 * @group mccabe
 * @group metric
 */
class MacCaybe extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider provideCCN
     */
    public function testICanCountComplexity($filename, $expectedCCN) {

        $loc = new McCabe(new \Hal\Component\Token\Tokenizer());
        $r = $loc->calculate($filename);
        $this->assertEquals($expectedCCN, $r->getCyclomaticComplexityNumber());
    }

    public function provideCCN() {
        return array(
            array(__DIR__.'/../../../../../resources/mccaybe/f1.php', 2)
            ,array(__DIR__.'/../../../../../resources/mccaybe/f2.php', 3)
            ,array(__DIR__.'/../../../../../resources/mccaybe/f3.php', 3)
        );
    }

    public function testMcCaybeResultCanBeConvertedToArray() {

        $result = new \Hal\Metrics\Complexity\Component\McCabe\Result();
        $array = $result->asArray();

        $this->assertArrayHasKey('cyclomaticComplexity', $array);
    }

    /**
     * @dataProvider provideCCNPHP7
     * @group php7
     */
    public function testICanCountComplexityOfAnonymousClass($filename, $expectedCCN) {

        $loc = new McCabe(new \Hal\Component\Token\Tokenizer());
        $r = $loc->calculate($filename);
        $this->assertEquals($expectedCCN, $r->getCyclomaticComplexityNumber());
    }

    public function provideCCNPHP7() {
        return array(
            array(__DIR__.'/../../../../../resources/mccaybe/php7-1.php', 4)
        );
    }
}