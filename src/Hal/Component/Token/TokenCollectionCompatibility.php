<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Token;

/**
 * Representation of Collection of tokens
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class TokenCollectionCompatibility {


    /**
     * @param array $tokens
     * @return array
     */
    public function decorate(array $tokens)
    {
        if(-1 !== version_compare(PHP_VERSION, '7.0.0')) {
            return $tokens;
        }

        $previous = null;
        foreach($tokens as $index => &$token) {

            // coalesce operator
            if(T_STRING == $token->getType()&&'?' == $token->getValue()
                && $index > 0
                && T_STRING == $previous->getType()&&'?' == $previous->getValue()
            ) {
                unset($tokens[$index]);
                $tokens[$index - 1] = new Token(array(T_COALESCE, T_COALESCE));
                continue;
            }


            // spaceship operator
            if($index > 2
                && T_STRING == $tokens[$index]->getType() && '>' === $tokens[$index]->getValue()
                && T_IS_SMALLER_OR_EQUAL == $previous->getType()
            ) {
                unset($tokens[$index]);
                $tokens[$index - 1] = new Token(array(T_SPACESHIP, T_SPACESHIP));
                continue;
            }

            $previous = $token;
        }
        return array_values($tokens);
    }
}
