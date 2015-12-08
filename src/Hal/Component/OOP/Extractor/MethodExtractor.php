<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Extractor;
use Hal\Component\OOP\Reflected\MethodUsage;
use Hal\Component\OOP\Reflected\ReflectedArgument;
use Hal\Component\OOP\Reflected\ReflectedClass\ReflectedAnonymousClass;
use Hal\Component\OOP\Reflected\ReflectedMethod;
use Hal\Component\OOP\Reflected\ReflectedReturn;
use Hal\Component\OOP\Resolver\TypeResolver;
use Hal\Component\Token\TokenCollection;


/**
 * Extracts info about classes in one file
 * Remember that one file can contains multiple classes
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class MethodExtractor implements ExtractorInterface {

    /**
     * @var Searcher
     */
    private $searcher;

    /**
     * Constructor
     *
     * @param Searcher $searcher
     */
    public function __construct(Searcher $searcher)
    {
        $this->searcher = $searcher;
    }

    /**
     * Extract method from position
     *
     * @param int $n
     * @param TokenCollection$tokens
     * @return ReflectedMethod
     * @throws \Exception
     */
    public function extract(&$n, TokenCollection $tokens)
    {
        $start = $n;

        $declaration = $this->searcher->getUnder(array(')'), $n, $tokens);
        if(!preg_match('!function\s+(.*)\(\s*(.*)!is', $declaration, $matches)) {
            throw new \Exception(sprintf("Closure detected instead of method\nDetails:\n%s", $declaration));
        }
        list(, $name, $args) = $matches;
        $method = new ReflectedMethod($name);

        // visibility
        $this->extractVisibility($method, $p = $start, $tokens); // please keep "p = start"

        // state
        $this->extractState($method, $p = $start, $tokens); // please keep "p = start"

        $arguments = preg_split('!\s*,\s*!m', $args);
        foreach($arguments as $argDecl) {

            if(0 == strlen($argDecl)) {
                continue;
            }

            $elems = preg_split('!([\s=]+)!', $argDecl);
            $isRequired = 2 == sizeof($elems, COUNT_NORMAL);

            if(sizeof($elems, COUNT_NORMAL) == 1) {
                list($name, $type) = array_pad($elems, 2, null);
            } else {
                if('$' == $elems[0][0]) {
                    $name = $elems[0];
                    $type  = null;
                    $isRequired = false;
                } else {
                    list($type, $name) = array_pad($elems, 2, null);
                }
            }

            $argument = new ReflectedArgument($name, $type, $isRequired);
            $method->pushArgument($argument);
        }



        // does method has body ? (example: interface ; abstract classes)
        $p = $n  + 1;
        $underComma = trim($this->searcher->getUnder(array(';'), $p, $tokens));
        if(strlen($underComma) > 0) {
            //
            // Body
            $this->extractContent($method, $n, $tokens);

            // Calls
            $this->extractCalls($method, $n, $tokens);

            // Tokens
            $end = $this->searcher->getPositionOfClosingBrace($n, $tokens);
            if($end > 0) {
                $method->setTokens($tokens->extract($n, $end));
            }
        } else {
            $method->setTokens($tokens->extract(0, $n));
        }

        //
        // Dependencies
        $this->extractDependencies($method, 0, $method->getTokens());

        // returns
        $p = $start;
        $this->extractReturns($method, $p, $tokens);

        // usage
        $this->extractUsage($method);

        return $method;
    }

    /**
     * Extracts visibility
     *
     * @param ReflectedMethod $method
     * @param $n
     * @param TokenCollection $tokens
     * @return $this
     */
    public function extractVisibility(ReflectedMethod $method, $n, TokenCollection $tokens) {
        switch(true) {
            case $this->searcher->isPrecededBy(T_PRIVATE, $n, $tokens, 4):
                $visibility = ReflectedMethod::VISIBILITY_PRIVATE;
                break;
            case $this->searcher->isPrecededBy(T_PROTECTED, $n, $tokens, 4):
                $visibility = ReflectedMethod::VISIBILITY_PROTECTED;
                break;
        case $this->searcher->isPrecededBy(T_PUBLIC, $n, $tokens, 4):
                default:
                $visibility = ReflectedMethod::VISIBILITY_PUBLIC;
                break;
        }
        $method->setVisibility($visibility);
        return $this;
    }

    /**
     * Extracts state
     *
     * @param ReflectedMethod $method
     * @param $n
     * @param TokenCollection $tokens
     * @return $this
     */
    public function extractState(ReflectedMethod $method, $n, TokenCollection $tokens) {
        if($this->searcher->isPrecededBy(T_STATIC, $n, $tokens, 4)) {
            $method->setState(ReflectedMethod::STATE_STATIC);
        }
        return $this;
    }

    /**
     * Extracts content of method
     *
     * @param ReflectedMethod $method
     * @param integer $n
     * @param TokenCollection $tokens
     * @return $this
     */
    private function extractContent(ReflectedMethod $method, $n, TokenCollection $tokens) {
        $end = $this->searcher->getPositionOfClosingBrace($n, $tokens);
        if($end > 0) {
            $collection = $tokens->extract($n, $end);
            $method->setContent($collection->asString());
        }
        return $this;
    }

    /**
     * Extracts content of method
     *
     * @param ReflectedMethod $method
     * @param integer $n
     * @param TokenCollection $tokens
     * @return $this
     */
    private function extractDependencies(ReflectedMethod $method, $n, TokenCollection $tokens) {

        //
        // Object creation
        $extractor = new CallExtractor($this->searcher);
        $start = $n;
        $len = sizeof($tokens, COUNT_NORMAL);
        for($i = $start; $i < $len; $i++) {
            $token = $tokens[$i];
            switch($token->getType()) {
                case T_PAAMAYIM_NEKUDOTAYIM:
                case T_NEW:
                    $call = $extractor->extract($i, $tokens);
                    if($call !== 'class') { // anonymous class
                        $method->pushDependency($call);
                        $method->pushInstanciedClass($call);
                    }
                    break;
            }
        }

        //
        // Parameters in Method API
        $resolver = new TypeResolver();
        foreach($method->getArguments() as $argument) {
            $name = $argument->getType();
            if(strlen($name) > 0 && !$resolver->isNative($name)) {
                $method->pushDependency($name);
            }
        }

        return $this;
    }

    /**
     * Extracts calls of method
     *
     * @param ReflectedMethod $method
     * @param integer $n
     * @param TokenCollection $tokens
     * @return $this
     */
    private function extractCalls(ReflectedMethod $method, $n, TokenCollection $tokens) {

        // $this->foo(), $c->foo()
        if(preg_match_all('!(\$[\w]*)\-\>(\w*?)\(!', $method->getContent(), $matches, PREG_SET_ORDER)) {
            foreach($matches as $m) {
                $function = $m[2];
                if('$this' == $m[1]) {
                    $method->pushInternalCall($function);
                } else {
                    $method->pushExternalCall($m[1], $function);
                }
            }
        }
        // (new X)->foo()
        if(preg_match_all('!\(new (\w+?).*?\)\->(\w+)\(!', $method->getContent(), $matches, PREG_SET_ORDER)) {
            foreach($matches as $m) {
                $method->pushExternalCall($m[1], $m[2]);
            }
        }
    }

    /**
     * Extract the list of returned values
     *
     * @param ReflectedMethod $method
     * @return $this
     */
    private function extractReturns(ReflectedMethod $method, $n, TokenCollection $tokens) {

        $resolver = new TypeResolver();

        // PHP 7
        // we cannot use specific token. The ":" delimiter is a T_STRING token
        $following = $this->searcher->getUnder(array('{', ';'), $n, $tokens);
        if(preg_match('!:(.*)!', $following, $matches)) {
            $type = trim($matches[1]);
            if(empty($type)) {
                return $this;
            }
            $return = new ReflectedReturn($type, ReflectedReturn::VALUE_UNKNOW, ReflectedReturn::STRICT_TYPE_HINT);
            $method->pushReturn($return);
            return $this;
        }

        // array of available values based on code
        if(preg_match_all('!([\s;]return\s|^return\s+)(.*?);!', $method->getContent(), $matches)) {
            foreach($matches[2] as $m) {
                $value = trim($m, ";\t\n\r\0\x0B");
                $return = new ReflectedReturn($resolver->resolve($m), $value, ReflectedReturn::ESTIMATED_TYPE_HINT);
                $method->pushReturn($return);
            }
        }
        return $this;
    }

    /**
     * Extracts usage of method
     *
     * @param ReflectedMethod $method
     * @return $this
     */
    private function extractUsage(ReflectedMethod $method) {
        $tokens = $method->getTokens();
        $codes = $values = array();
        foreach($tokens as $token) {
            if(in_array($token->getType(), array(T_WHITESPACE, T_BOOL_CAST, T_INT_CAST, T_STRING_CAST, T_DOUBLE_CAST, T_OBJECT_CAST))) {
                continue;
            }
            array_push($codes, $token->getType());
            array_push($values, $token->getValue());
        }
        switch(true) {
            case preg_match('!^(get)|(is)|(has).*!',$method->getName()) && $codes == array(T_RETURN, T_VARIABLE, T_OBJECT_OPERATOR, T_STRING, T_STRING):
                $method->setUsage(MethodUsage::USAGE_GETTER);
                break;
            // basic setter
            case preg_match('!^set.*!',$method->getName()) && $codes == array(T_VARIABLE, T_OBJECT_OPERATOR,T_STRING,T_STRING, T_VARIABLE, T_STRING) && $values[3] == '=':
            // fluent setter
            case preg_match('!^set.*!',$method->getName()) && $codes == array(T_VARIABLE, T_OBJECT_OPERATOR,T_STRING,T_STRING, T_VARIABLE, T_STRING, T_RETURN, T_VARIABLE, T_STRING)
                && $values[3] == '=' && $values[7] == '$this':
                $method->setUsage(MethodUsage::USAGE_SETTER);
                break;
            default:
                $method->setUsage(MethodUsage::USAGE_UNKNWON);
        }
        return $this;
    }
}
