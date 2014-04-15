<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Extractor;
use Hal\Component\Token\Token;


/**
 * Tool class
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Searcher {

    /**
     * Get value under limiters
     *
     * @param string[] $delimiters
     * @param int $n
     * @param array $tokens
     * @return null|string
     */
    public function getUnder(array $delimiters, &$n, $tokens) {
        $end = sizeof($tokens, COUNT_NORMAL);
        $value = '';
        while($n < $end) {
            $token = new Token($tokens[$n]);
            if(in_array($token->getValue(), $delimiters)) {
                return $value;
            }
            $value .= $token->getValue();
            $n++;
        }
        return null;
    }

    /**
     * Get name following token
     *
     * @param $n
     * @param $tokens
     * @return null|string
     */
    public function getFollowingName(&$n, $tokens) {
        $n = $n + 2;
        return $this->getUnder(array('{', ' ', ';'), $n, $tokens);
    }

};