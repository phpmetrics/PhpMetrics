<?php

namespace Test;
use Hal\Component\Parser\Helper\NamespaceReplacer;
use Hal\Component\Parser\Token;
use Hal\Component\Parser\Tokenizer;

/**
 * @group parser
 * @group namespace
 */
class NamespaceReplacerTest extends \PHPUnit_Framework_TestCase {

    public function testICanGetNamespaces() {

        $values = array(
            'My\\C1' => 'My\\C1'
        , 'AliasedAnother' => '\\My\\Another'
        , 'C2' => 'C2'
        , 'AliasedC3' => 'C3'
        );

        $resolver = $this->getMockBuilder('Hal\Component\Parser\Resolver\NamespaceResolver')->disableOriginalConstructor()->getMock();
        $resolver->method('all')->will($this->returnValue($values));

        $code = <<<EOT
<?php

namespace Ns1\My;

use My\C1;
use \My\Another as AliasedAnother;
use C2;
use C3 as AliasedC3;

new AliasedAnother;
new \AliasedAnother;
new C2;
new \C2;
EOT;


        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $replacer = new NamespaceReplacer($resolver);
        $result = $replacer->replace($tokens);

        $expected = array(
            Token::T_NAMESPACE,
            'Ns1\My',
            Token::T_NEW, '\My\Another',
            Token::T_NEW, '\AliasedAnother',
            Token::T_NEW, 'C2',
            Token::T_NEW, '\C2',
        );

        $this->assertEquals($expected, $result);
    }

}