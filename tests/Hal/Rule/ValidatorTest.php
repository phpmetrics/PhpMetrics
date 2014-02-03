<?php
namespace Test\Hal\Token;

use Hal\Rule\RuleSet;
use Hal\Rule\Validator;
use Hal\Token\TokenType;

/**
 * @group rule
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider provideRuleset
     */
    public function testICanValidateByRule($rule, $value, $expected) {

        $ruleSet = $this->getMock('\Hal\Rule\RuleSet');
        $ruleSet->expects($this->once())->method('getRule')->will($this->returnValue($rule));

        $validator = new Validator($ruleSet);

        $result = $validator->validate('any', $value);
        $this->assertEquals($expected, $result);
    }

    public function provideRuleset() {
        return array(
            array( array(0,10,20)   , 0         , Validator::CRITICAL)
            , array( array(0,10,20)   , 1       , Validator::CRITICAL)
            , array( array(0,10,20)   , 10      , Validator::WARNING)
            , array( array(0,10,20)   , 11      , Validator::WARNING)
            , array( array(0,10,20)   , 20      , Validator::GOOD)
            , array( array(0,10,20)   , 100     , Validator::GOOD)

            , array( array(20, 10, 5)   , 0     , Validator::GOOD)
            , array( array(20, 10, 5)   , 5     , Validator::WARNING)
            , array( array(20, 10, 5)   , 7      , Validator::WARNING)
            , array( array(20, 10, 5)   , 10     , Validator::WARNING)
            , array( array(20, 10, 5)   , 20     , Validator::CRITICAL)
            , array( array(20, 10, 5)   , 100     , Validator::CRITICAL)

            , array( null   , 100     , Validator::UNKNOWN)
        );
    }
}