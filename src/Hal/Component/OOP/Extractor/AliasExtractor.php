<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Extractor;
use Hal\Component\OOP\Reflected\ReflectedMethod;
use Hal\Component\Token\TokenCollection;


/**
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class AliasExtractor implements ExtractorInterface {

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
     * Extract alias from position
     *
     * @param $n
     * @param TokenCollection $tokens
     * @return ReflectedMethod
     */
    public function extract(&$n, TokenCollection $tokens)
    {
        $expression = $this->searcher->getUnder(array(';'), $n, $tokens);
        if(preg_match('!use\s+(.*)\s+as\s+(.*)!i', $expression, $matches)) {
            list(, $real, $alias) = $matches;
        } else if(preg_match('!use\s+(.*)\s*!i', $expression, $matches)) {
            list(, $real) = $matches;
            $alias = $real;
        }


        return (object) array(
            'name' => $real
            , 'alias' => $alias
        );
    }
};