<?php
namespace Test\Hal\Component\Token;

use Hal\Application\Rule\RuleSet;
use Hal\Application\Rule\Validator;
use Hal\Component\Token\TokenType;

/**
 * @group rule
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider provideRuleset
     */
    public function testICanValidateByRule($rule, $value, $expected) {

        $ruleSet = $this->getMock('\Hal\Application\Rule\RuleSet');
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
            
            , array( array(20, 10, 5)    , null    , Validator::UNKNOWN)
            , array( array(20, 10, 5)    , false   , Validator::UNKNOWN)
            , array( array(20, 10, 5)    , true    , Validator::UNKNOWN)
            , array( null                , 100     , Validator::UNKNOWN)
            , array( true                , 100     , Validator::UNKNOWN)
            , array( false               , 100     , Validator::UNKNOWN)
        );
    }
}