<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Component\Myer;

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
     * @param TokenCollection $tokens
     * @return Result
     */
    public function calculate($tokens)
    {
        $mcCabe = new McCabe();
        $result = new Result;

        // Cyclomatic complexity
        $cc = $mcCabe->calculate($tokens);

        // Number of operator
        $L = 0;
        $logicalOperators = array(
            T_BOOLEAN_AND => T_BOOLEAN_AND
            , T_LOGICAL_AND => T_LOGICAL_AND
            , T_BOOLEAN_OR => T_BOOLEAN_OR
        , T_LOGICAL_OR => T_LOGICAL_OR
        );
        foreach($tokens as $token) {
            if(isset($logicalOperators[$token->getType()])) {
                $L++;
            }
        }

        $result
            ->setNumberOfOperators($L)
            ->setMcCabe($cc);

        return $result;
    }
}
