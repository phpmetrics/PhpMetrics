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
     * @param \Hal\Component\Token\TokenCollection $tokens
     * @return null|string
     */
    public function getUnder(array $delimiters, &$n, TokenCollection $tokens) {
        $end = sizeof($tokens, COUNT_NORMAL);
        $value = '';
        while($n < $end) {
            $token = $tokens[$n];
            if(in_array($token->getValue(), $delimiters)) {
                return $value;
            }
            $value .= $token->getValue();
            $n++;
        }
        return null;
    }

    /**
     * Get non whitespace previous token
     *
     * @param $n
     * @param \Hal\Component\Token\TokenCollection $tokens
     * @return null
     */
    public function getPrevious(&$n, TokenCollection $tokens) {
        $p = $n - 1;
        for($i = $p ; $i > 0; $i--) {
            if(T_WHITESPACE !== $tokens[$i]->getType()) {
                return $tokens[$i];
            }
        }
        return null;
    }

    /**
     * Get name following token
     *
     * @param $n
     * @param \Hal\Component\Token\TokenCollection $tokens
     * @return null|string
     */
    public function getFollowingName(&$n, TokenCollection $tokens) {
        $n = $n + 2;
        return $this->getUnder(array('{', ' ', ';'), $n, $tokens);
    }

    /**
     * Get the position of the brace which closes the next brace
     *
     * @param $n
     * @param TokenCollection $tokens
     * @return null
     */
    public function getPositionOfClosingBrace(&$n, TokenCollection $tokens) {
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
                            $start = $n = $i + 1;
                        }
                        break;
                    case '}':
                        $openBrace--;
                        if($openBrace <= 0) {
                            return $i;
                        }
                        break;
                }
            }
        }
        return null;
    }
};