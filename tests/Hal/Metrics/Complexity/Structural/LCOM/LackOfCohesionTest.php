<?php
namespace Test\Hal\Metrics\Complexity\Structural\LCOM;
use Hal\Component\OOP\Extractor\Extractor;
use Hal\Component\Token\Tokenizer;
use Hal\Metrics\Complexity\Structural\LCOM\LackOfCohesionOfMethods;

/**
 * @group metrics
 * @group oop
 * @group lcom
 */
class LackOfCohesionTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider providesFilesForLcom
     */
    public function testICanCalculateLackOfCohesionOfClass($filename, $expected) {

        $tokens = (new \Hal\Component\Token\Tokenizer())->tokenize($filename);
        $extractor = new Extractor();
        $result = $extractor->extract($tokens);
        $classes = $result->getClasses();
        $class = $classes[0];

        $lcom = new LackOfCohesionOfMethods();
        $result = $lcom->calculate($class);

        $this->assertEquals($expected, $result->getLCOM());
    }


    public function providesFilesForLcom() {
        return array(
            array(__DIR__.'/../../../../../resources/lcom/f1.php', 2)
            , array(__DIR__.'/../../../../../resources/lcom/f2.php', 1)
            , array(__DIR__.'/../../../../../resources/lcom/f3.php', 3)
            , array(__DIR__.'/../../../../../resources/lcom/f4.php', 2)
            , array(__DIR__.'/../../../../../resources/lcom/f5.php', 2)
        );
    }
}
