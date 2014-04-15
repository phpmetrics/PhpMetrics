<?php
namespace Test\Hal\Component\Token;

use Hal\Application\Rule\RuleSet;
use Hal\Application\Rule\Validator;
use Hal\Component\Token\TokenType;

/**
 * @group rule
 */
class RuleSetTest extends \PHPUnit_Framework_TestCase {

    public function testICanReadRule() {
        $ruleset = new RuleSet(array('rule1' => array (1, 2,3)));
        $this->assertEquals(array(1,2,3), $ruleset->getRule('rule1'));
        $this->assertEquals(null, $ruleset->getRule('noDeclared'));
    }
}