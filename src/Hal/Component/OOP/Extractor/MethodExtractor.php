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
use Hal\Component\Token\Token;
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
        $method->setContent($this->extractContent($n, $tokens));

        return $method;
    }

    /**
     * Extracts content of method
     *
     * @param $n
     * @param TokenCollection $tokens
     * @return null|string
     */
    private function extractContent(&$n, TokenCollection $tokens) {
        // search the end of the method
        $openBrace = 0;
        $start = null;
        $len = sizeof($tokens);
        for($i = $n; $i < $len; $i++) {
            $token = $tokens[$i];
            if(T_STRING == $token->getType()) {
                switch($token->getValue()) {
                    case '{':
                        $openBrace++;
                        if(is_null($start)) {
                            $start = $i + 1;
                        }
                        break;
                    case '}':
                        $openBrace--;
                        if($openBrace <= 0) {
                            $concerned = array_slice($tokens->asArray(), $start, $i - $start );
                            $collection = new TokenCollection($concerned);
                            return $collection->asString();
                        }
                        break;
                }
            }
        }

        return null;
    }
}