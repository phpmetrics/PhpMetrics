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
     * @param int $startingToken
     * @param \Hal\Component\Token\TokenCollection $tokens
     * @return null|string
     */
    public function getUnder(array $delimiters, &$startingToken, TokenCollection $tokens) 
    {
        $end = sizeof($tokens, COUNT_NORMAL);
        $value = '';
        while($startingToken < $end) {
            $token = $tokens[$startingToken];
            if(in_array($token->getValue(), $delimiters)) {
                return $value;
            }
            $value .= $token->getValue();
            $startingToken++;
        }
        
        return null;
    }

    /**
     * Get non whitespace previous token
     *
     * @param int $startingToken
     * @param \Hal\Component\Token\TokenCollection $tokens
     * @return null
     */
    public function getPrevious(&$startingToken, TokenCollection $tokens)
    {
        $p = $startingToken - 1;
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
     * @param int $startingToken
     * @param \Hal\Component\Token\TokenCollection $tokens
     * @return null|string
     */
    public function getFollowingName(&$startingToken, TokenCollection $tokens)
    {
        $startingToken = $startingToken + 2;
        
        return $this->getUnder(array('{', ' ', ';', '(', '::'), $startingToken, $tokens);
    }

    /**
     * Get the position of the brace which closes the next brace
     *
     * @param int $startingToken
     * @param TokenCollection $tokens
     * @return null|int
     */
    public function getPositionOfClosingBrace(&$startingToken, TokenCollection $tokens)
    {
        // search the end of the method
        $openBrace = 0;
        $start = null;
        $len = sizeof($tokens);
        for($i = $startingToken; $i < $len; $i++) {
            $token = $tokens[$i];
            if(T_STRING == $token->getType()) {
                switch($token->getValue()) {
                    case '{':
                        $openBrace++;
                        if(is_null($start)) {
                            $start = $startingToken = $i + 1;
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

    /**
     * 
     * @param TokenCollection $tokens
     * @param int $startingToken
     * @return null|int 
     */
    public function getExtendPosition(TokenCollection $tokens, $startingToken)
    {
        for($i = $startingToken; $i > 0; $i--) {
            $token = $tokens[$i];
            if ($token->getValue() === 'extends') {
                return $i;
            }
        }
        
        return null;
    }
    /**
     * 
     * @param TokenCollection $tokens
     * @return int|null
     */
    public function getClassNamePosition(TokenCollection $tokens)
    {
        $len = sizeof($tokens);
        for($i = 0; $i < $len; $i++) {
            $token = $tokens[$i];
            if ($token->getValue() === 'class') {
                return $i;
            }
        }
        
        return null;
    }

    /**
     * 
     * @param int $tokenType
     * @param int $startingToken
     * @param TokenCollection $tokens
     * @return null|int
     */
    public function getPositionOfPrevious($tokenType, $startingToken, TokenCollection $tokens)
    {
        for($i = $startingToken; $i > 0; $i--) {
            if($tokenType == $tokens->get($i)->getType()) {
                return $i;
            }
        }
        
        return null;
    }

    /**
     * 
     * @param int $tokenType
     * @param int $startingToken
     * @param TokenCollection $tokens
     * @return null|int
     */
    public function getPositionOfNext($tokenType, $startingToken, TokenCollection $tokens) 
    {
        $len = sizeof($tokens);
        for($i = $startingToken; $i < $len; $i++) {
            if($tokenType == $tokens->get($i)->getType()) {
                return $i;
            }
        }
        
        return null;
    }

    /**
     * 
     * @param int $tokenType
     * @param int $startingToken
     * @param TokenCollection $tokens
     * @param int $limit
     * @return bool
     */
    public function isPrecededBy($tokenType, $startingToken, TokenCollection $tokens, $limit = 2)
    {
        $position = $this->getPositionOfPrevious($tokenType, $startingToken, $tokens);
        
        return ($startingToken - $position <= $limit);
    }
    
    /**
     * 
     * @param int $tokenType
     * @param int $startingToken
     * @param TokenCollection $tokens
     * @param int $limit
     * @return bool
     */
    public function isFollowedBy($tokenType, $startingToken, TokenCollection $tokens, $limit = 2)
    {
        $position = $this->getPositionOfNext($tokenType, $startingToken, $tokens);
        
        return ($position - $startingToken >= $limit);
    }
}
