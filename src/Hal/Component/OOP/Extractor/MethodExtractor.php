<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Extractor;
use Hal\Component\OOP\Reflected\ReflectedArgument;
use Hal\Component\OOP\Reflected\ReflectedMethod;
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
     */
    public function extract(&$n, TokenCollection $tokens)
    {
        $declaration = $this->searcher->getUnder(array(')'), $n, $tokens);
        if(!preg_match('!function\s+(.*)\(\s*(.*)!is', $declaration, $matches)) {
            throw new \Exception('Closure detected instead of method');
        }
        list(, $name, $args) = $matches;
        $method = new ReflectedMethod($name);

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
                list($type, $name) = array_pad($elems, 2, null);

            }

            $argument = new ReflectedArgument($name, $type, $isRequired);
            $method->pushArgument($argument);
        }

        //
        // Body
        $this->extractContent($method, $n, $tokens);

        // Tokens
        $end = $this->searcher->getPositionOfClosingBrace($n, $tokens);
        if($end > 0) {
            $method->setTokens($tokens->extract($n, $end));
        }

        //
        // Dependencies
        $this->extractDependencies($method, $n, $tokens);

        // returns
        $this->extractReturns($method, $method->getContent());

        return $method;
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
        $dependencies = array();
        $extractor = new CallExtractor($this->searcher);
        $start = $n;
        $len = sizeof($tokens, COUNT_NORMAL);
        for($i = $start; $i < $len; $i++) {
            $token = $tokens[$i];
            switch($token->getType()) {
                case T_PAAMAYIM_NEKUDOTAYIM:
                case T_NEW:
                    $call = $extractor->extract($i, $tokens);
                    $method->pushDependency($call);
                    break;
            }
        }

        //
        // Parameters in Method API
        foreach($method->getArguments() as $argument) {
            $name = $argument->getType();
            if(!in_array($argument->getType(), array(null, 'array'))) {
                $method->pushDependency($name);
            }
        }

        return $this;
    }

    /**
     * Extract the list of returned values
     *
     * @param string $content
     * @return $this
     */
    private function extractReturns(ReflectedMethod $method, $content) {
        if(preg_match_all('!([\s;]return\s|^return\s)!', $content, $matches)) {
            foreach($matches[1] as $m) {
                $method->pushReturn($m);
            }
        }
        return $this;
    }
}