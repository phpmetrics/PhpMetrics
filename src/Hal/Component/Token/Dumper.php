<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Token;

/**
 * Way to dump tokens
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Dumper {


    /**
     * @param TokenCollection $tokens
     * @param int $start
     * @param int $len
     * @return string
     */
    public function dump(TokenCollection $tokens, $start = 0, $len = 0)
    {
        $str = '';
        if($len == 0) {
            $len = sizeof($tokens);
        }
        if($start < 0) {
            $start = 0;
        }
        for($i = $start; $i < $len; $i++) {
            $str .= sprintf("\n%s : %s", token_name($tokens[$i]->getType()), $tokens[$i]->getValue());
        }
        return $str;
    }

}