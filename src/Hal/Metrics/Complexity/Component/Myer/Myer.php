<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Component\Myer;

use Hal\Component\Reflected\Klass;
use Hal\Component\Token\Token;
use Hal\Metrics\ClassMetric;
use Hal\Metrics\Complexity\Component\McCabe\McCabe;

/**
 * Calculates myer's interval (extension of McCabe cyclomatic complexity)
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Myer implements ClassMetric {

    /**
     * Calculates Myer's interval
     *
     *      Cyclomatic complexity : Cyclomatic complexity + L
     *      where L is the number of logical operators
     *
     * @param Klass $class
     * @return Result
     * @internal param array $tokens
     */
    public function calculate(Klass $class)
    {
        $mcCabe = new McCabe();
        $result = new Result;

        // Cyclomatic complexity
        $cc = $mcCabe->calculate($class);

        // Number of operator
        $L = array_reduce($class->getTokens(), function($result, $item) {
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
