<?php
namespace Test\Hal\Component\Token;

use Hal\Component\Token\Token;

/**
 * @group token
 */
class TokenTest extends \PHPUnit_Framework_TestCase {


    /**
     * @dataProvider providesTokens
     */
    public function testICanWorkWithDifferentKindOfTokens($data) {
       $token = new Token($data);
       $this->assertNotNull($token->getType());
    }

    public function providesTokens() {
        return array(
            array(array(309, '$t', 18))
            , array('(')
        );
    }

}