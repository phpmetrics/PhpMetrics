<?php

namespace Test;
use Hal\Component\Parser\CodeParser;
use Hal\Component\Reflected\File;
use Hal\Component\Parser\Resolver\NamespaceResolver;
use Hal\Component\Parser\Searcher;
use Hal\Component\Token\Token;
use Hal\Component\Token\Tokenizer;

/**
 * @group parser
 */
class AnonymousClassesTest extends \PHPUnit_Framework_TestCase {

    public function testAnonymousClassIsFound()
    {
        $code = <<<EOT
namespace My;
class Mother {
    public function foo() {
        return 'abc';
    }
}

\$c = new class extends Mother {

};
assert('abc' === \$c->foo());
EOT;

        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);

        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);

        $classes = $result->getClasses();
        $mother = $classes[0];
        $anonymous = $classes[1];
        $this->assertEquals('\\My\\Mother', $mother->getFullName(), 'mother class is found');
        $this->assertEquals('class@anonymous', $anonymous->getName(), 'anonymous class is found');
        $this->assertEquals(array('\\My\\Mother'), $anonymous->getParents(), 'mother of anonymous class is found');
        $this->assertEquals('\\My', $anonymous->getNamespace(), 'anonymous class is in default namespace');
        $this->assertTrue($anonymous->isAnonymous());
    }
}