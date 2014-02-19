<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\OOP\Extractor;
use Hal\Token\Token;


/**
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class CallExtractor implements ExtractorInterface {

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
     * Extract dependency from call
     *
     * @param $n
     * @param array $tokens
     * @return string
     */
    public function extract(&$n, $tokens)
    {

        $token = new Token($tokens[$n]);
        switch($token->getType()) {
            case T_PAAMAYIM_NEKUDOTAYIM:
                $prev = $n - 1;
                return $this->searcher->getUnder(array('::'), $prev, $tokens);
                break;
            case T_NEW:
                return $this->searcher->getFollowingName($n, $tokens);
                break;
        }
        throw new \LogicException('Classname of call not found');
    }
};