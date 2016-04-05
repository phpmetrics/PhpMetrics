<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Component\McCabe;
use Hal\Component\Token\TokenCollection;

/**
 * Calculates cyclomatic complexity
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class McCabe {

    /**
     * Calculate cyclomatic complexity number
     *
     * We can calculate ccn in two ways (we choose the second):
     *
     *  1.  Cyclomatic complexity (CC) = E - N + 2P
     *      Where:
     *      P = number of disconnected parts of the flow graph (e.g. a calling program and a subroutine)
     *      E = number of edges (transfers of control)
     *      N = number of nodes (sequential group of statements containing only one transfer of control)
     *
     * 2. CC = Number of each decision point
     *
     * @param TokenCollection $tokens
     * @return Result
     */
    public function calculate($tokens)
    {
        $info = new Result;

        $ccn = 1; // default path
        foreach($tokens as $token) {

            switch($token->getType()) {
                case T_IF:
                case T_ELSEIF:
                case T_FOREACH:
                case T_FOR:
                case T_WHILE:
                case T_DO:
                case T_BOOLEAN_AND:
                case T_LOGICAL_AND:
                case T_BOOLEAN_OR:
                case T_LOGICAL_OR:
                case T_SPACESHIP:
                case T_CASE:
                case T_DEFAULT:
                case T_CATCH:
                case T_CONTINUE:
                    $ccn++;
                    break;
                case T_STRING:
                    if('?' == $token->getValue()) {
                        $ccn = $ccn + 2;
                    }
                    break;
                case T_COALESCE:
                    $ccn = $ccn + 2;
                    break;
            }

        }

        $info->setCyclomaticComplexityNumber(max(1, $ccn));
        return $info;
    }
}
