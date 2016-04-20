<?php
namespace Test\Hal\Metrics\Complexity\Text\Halstead;

use Hal\Metrics\Complexity\Text\Halstead\Halstead;
use Hal\Metrics\Complexity\Text\Halstead\Result;
use Hal\Component\Token\Tokenizer;
use Hal\Component\Token\TokenType;

/**
 * @group halstead
 * @group metric
 */
class HalsteadTest extends \PHPUnit_Framework_TestCase {

    public function testHalsteadServiceReturnsResult() {

        $class = $this->getMock('\Hal\Component\Reflected\Klass');
        $class->method('getTokens')->will($this->returnValue(array()));

        $object = new Halstead($this->getMock('\Hal\Component\Token\TokenType'));
        $this->assertInstanceOf("\\Hal\\Metrics\\Complexity\\Text\\Halstead\\Result", $object->calculate($class));
    }

    public function testHalsteadResultCanBeConvertedToArray() {

        $result = new Result();
        $array = $result->asArray();

        $this->assertArrayHasKey('volume', $array);
        $this->assertArrayHasKey('length', $array);
        $this->assertArrayHasKey('vocabulary', $array);
        $this->assertArrayHasKey('effort', $array);
        $this->assertArrayHasKey('difficulty', $array);
        $this->assertArrayHasKey('time', $array);
        $this->assertArrayHasKey('bugs', $array);
    }

    /**
     * @dataProvider provideFilesAndCounts
     */
    public function testHalsteadGiveValidValues($filename, $N1, $N2, $n1, $n2, $N, $V, $L, $D, $E, $T, $I) {
        $tokenType = new TokenType(); // please don't mock this: it make no sense else

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize(file_get_contents($filename));
        $class = $this->getMock('\Hal\Component\Reflected\Klass');
        $class->method('getTokens')->will($this->returnValue($tokens));

        $halstead = new Halstead($tokenType);
        $r = $halstead->calculate($class);

        $this->assertEquals($N1, $r->getNumberOfOperators(), 'number of operators');
        $this->assertEquals($n1, $r->getNumberOfUniqueOperators(), 'number of unique operators');
        $this->assertEquals($N2, $r->getNumberOfOperands(), 'number of operands');
        $this->assertEquals($n2, $r->getNumberOfUniqueOperands(), 'number of unique operands');

        $this->assertEquals($N, $r->getLength(), 'length');
        $this->assertEquals($V, $r->getVolume(), 'volume');
        $this->assertEquals($L, $r->getLevel(), 'level');
        $this->assertEquals($D, $r->getDifficulty(), 'difficulty');
        $this->assertEquals($E, $r->getEffort(), 'effort');
        $this->assertEquals($E, $r->getEffort(), 'effort');
        $this->assertEquals($T, $r->getTime(), 'time');
        $this->assertEquals($I, $r->getIntelligentContent(), 'intelligent content');

    }

    public function provideFilesAndCounts() {
        return array(
            array(__DIR__.'/../../../../../resources/halstead/f1.php', 4 , 3, 4, 2        , 7     , 18.09     , 0.33   , 3     , 54.28     , 3    , 6.03 ) // twice
            , array(__DIR__.'/../../../../../resources/halstead/f2.php', 8, 6, 5, 2       , 14    , 39.3      , 0.13  , 7.5  , 294.77    , 16    , 5.24) // max
            , array(__DIR__.'/../../../../../resources/halstead/f4.php', 14, 13, 9, 5     , 27    , 102.8    , 0.09   , 11.7  , 1202.74   , 67   , 8.79) // f_while
            , array(__DIR__.'/../../../../../resources/halstead/f3.php', 15, 8, 5, 4     , 23    , 72.91, 0.2  , 5 , 364.54    , 20, 14.58) // f_switch
        );
    }
}
