<?php

namespace Test;
use Hal\Component\Parser\Resolver\NamespaceResolver;
use Hal\Component\Parser\Tokenizer;

/**
 * @group parser
 * @group namespace
 */
class NamespaceResolverTest extends \PHPUnit_Framework_TestCase {

    public function testICanGetNamespaces() {
        $code = <<<EOT
<?php

namespace Ns\Demo;

use My\C1;
use My\Another as AliasedAnother;
use C2;
use C3 as AliasedC3;
use \C4 as AliasedC4;
class A
{
}
EOT;


        $expected = array(
            'My\\C1' => 'My\\C1'
            , 'AliasedAnother' => 'My\\Another'
            , 'C2' => 'C2'
            , 'AliasedC3' => 'C3'
            , 'AliasedC4' => '\C4'
        );

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $resolver = new NamespaceResolver($tokens);
        $this->assertEquals($expected, $resolver->all());
        $this->assertEquals('\\Ns\\Demo', $resolver->getCurrentNamespace());
    }



    /**
     * @dataProvider providesAliases
     */
    public function testResolverResolvesAliases($alias, $expected)
    {

        $code = <<<EOT
namespace My;

use C1;
use \My\Another as AliasedAnother;
use \C2;
use C3 as AliasedC3;
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $resolver = new NamespaceResolver($tokens);
        $this->assertEquals('\\My', $resolver->getCurrentNamespace());
        $this->assertEquals($expected, $resolver->resolve($alias));
    }

    public function providesAliases() {
        return array(
            array('C1', '\\My\\C1'),
            array('AliasedAnother', '\\My\\Another'),
            array('C2', '\\C2'),
            array('AliasedC3', '\\My\\C3'),
        );
    }

}