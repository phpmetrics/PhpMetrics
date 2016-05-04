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
class UsageOfMethodTest extends \PHPUnit_Framework_TestCase {

    public function testGettersAndSettersAreFound() {
        $code = <<<EOT
class classA {
    private \$a;
    private \$b;

    public function getA()
    {
        return \$this->a;
    }

    public function setA(\$a)
    {
        \$this->a = \$a;
    }

    public function getB()
    {
        return \$this->b;
    }

    public function setB(\$b)
    {
        \$this->b = (string) \$b;
        return \$this;
    }

    public function foo() {
        return \$this->a;
    }

    public function isGood() {
        return \$this->a;
    }

    public function isTypedGood() {
        return (bool) \$this->a;
    }

    public function hasA() {
        return \$this->a;
    }
}

EOT;


        $tokenizer = new Tokenizer();
        $tokens = $tokenizer->tokenize($code);
        $parser = new CodeParser(new Searcher(), new NamespaceResolver($tokens));
        $result = $parser->parse($tokens);
        $classes = $result->getClasses();
        $this->assertEquals(1, sizeof($classes));
        $methods = $classes[0]->getMethods();
        $this->assertEquals(8, sizeof($methods));

        $this->assertFalse($methods['getA']->isSetter());
        $this->assertTrue($methods['getA']->isGetter());
        $this->assertFalse($methods['foo']->isSetter());
        $this->assertTrue($methods['foo']->isGetter());
        $this->assertTrue($methods['setA']->isSetter());
        $this->assertFalse($methods['setA']->isGetter());
        $this->assertTrue($methods['setB']->isSetter());
        $this->assertFalse($methods['setB']->isGetter());
    }
}