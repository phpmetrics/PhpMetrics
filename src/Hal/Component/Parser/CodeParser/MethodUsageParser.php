<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Parser\CodeParser;

use Hal\Component\Reflected\Method;
use Hal\Component\Parser\Resolver\NamespaceResolver;
use Hal\Component\Parser\Searcher;
use Hal\Component\Reflected\MethodUsage;

class MethodUsageParser
{

    /**
     * @var Searcher
     */
    private $searcher;

    /**
     * @var NamespaceResolver
     */
    private $namespaceResolver;

    /**
     * CodeParser constructor.
     * @param Searcher $searcher
     * @param NamespaceResolver $namespaceResolver
     */
    public function __construct(Searcher $searcher, NamespaceResolver $namespaceResolver)
    {
        $this->searcher = $searcher;
        $this->namespaceResolver = $namespaceResolver;
    }


    /**
     * @param Method $method
     * @return int
     */
    public function parse(Method $method) {

        $start = $this->searcher->getNext($method->getTokens(), 0, '{') + 1;
        $len = sizeof($method->getTokens());
        $tokens = array_slice($method->getTokens(), $start,  ($len - $start) - 1);

        foreach($tokens as $n => $token) {
            // replace $this->aaa by "class_attribute
            if(preg_match('!^\$this\->\w+$!', $token)) {
                $tokens[$n] = 'class_attribute';
            }

            // replace vars by "var"
            if(preg_match('!^\$\w+$!', $token) && $token != '$this') {
                $tokens[$n] = 'var';
            }
        }
        switch($tokens) {
            // getters
            case array('return', 'cast', 'class_attribute'):
            case array('return','class_attribute'):
                return MethodUsage::USAGE_GETTER;
                break;

            // setters
            case array('class_attribute', '=', 'var'):
            case array('class_attribute', '=', 'cast', 'var'):
            case array('class_attribute', '=', 'var', 'return', '$this'):
            case array('class_attribute', '=', 'cast', 'var', 'return', '$this'):
                return MethodUsage::USAGE_SETTER;
                break;
        }


        return MethodUsage::USAGE_UNKNWON;
    }
}