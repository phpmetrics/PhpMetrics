<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Extractor;
use Hal\Component\Token\TokenCollection;


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
     * @param TokenCollection $tokens
     * @return string
     * @throws \LogicException
     */
    public function extract(&$n, TokenCollection $tokens)
    {

        $token = $tokens[$n];
        switch($token->getType()) {
            case T_PAAMAYIM_NEKUDOTAYIM:
                $prev = $n - 1;
                $value = $this->searcher->getUnder(array('::'), $prev, $tokens);
                if ($value === 'parent' || $value === 'self') {
                    return null;
                }
                return $value;
            case T_NEW:
                return $this->searcher->getFollowingName($n, $tokens);
        }
        throw new \LogicException('Classname of call not found');
    }
};