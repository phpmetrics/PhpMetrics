<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Component\Myer;

use Hal\Component\Token\Token;
use Hal\Component\Token\TokenCollection;
use Hal\Metrics\Complexity\Component\McCabe\McCabe;

/**
 * Calculates myer's interval (extension of McCabe cyclomatic complexity)
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Myer {

    /**
     * Calculates Myer's interval
     *
     *      Cyclomatic complexity : Cyclomatic complexity + L
     *      where L is the number of logical operators
     *
     * @param array $tokens
     * @return Result
     */
    public function calculate($tokens)
    {
        $mcCabe = new McCabe();
        $result = new Result;

        // Cyclomatic complexity
        $cc = $mcCabe->calculate($tokens);

        // Number of operator
        $L = array_reduce($tokens, function($result, $item) {
            return
                (in_array($item, array(Token::T_BOOLEAN_AND, Token::T_BOOLEAN_OR, Token::T_LOGICAL_AND, Token::T_LOGICAL_OR)))
                ? $result + 1
                : $result;
        });

        $result
            ->setNumberOfOperators($L)
            ->setMcCabe($cc);

        return $result;
    }
}
