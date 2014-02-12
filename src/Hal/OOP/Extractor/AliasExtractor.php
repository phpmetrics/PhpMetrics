<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\OOP\Extractor;
use Hal\OOP\Reflected\ReflectedArgument;
use Hal\OOP\Reflected\ReflectedClass;
use Hal\OOP\Reflected\ReflectedMethod;
use Hal\Token\Token;


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
     * @param array $tokens
     * @return ReflectedMethod
     */
    public function extract(&$n, $tokens)
    {
        $expression = $this->searcher->getUnder(array(';'), $n, $tokens);
        preg_match('!use\s+(.*)\s+as\s+(.*)!', $expression, $matches);
        list(, $real, $alias) = $matches;
        return (object) array(
            'name' => $real
            , 'alias' => $alias
        );
    }
};