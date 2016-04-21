<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Component\McCabe;
use Hal\Component\Reflected\Klass;
use Hal\Component\Token\Token;
use Hal\Metrics\ClassMetric;

/**
 * Calculates cyclomatic complexity
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class McCabe implements ClassMetric {

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
     * @param Klass $class
     * @return Result
     */
    public function calculate(Klass $class)
    {
        $info = new Result;

        $ccn = 1; // default path
        foreach($class->getTokens() as $token) {

            switch($token) {
                case Token::T_IF:
                case Token::T_ELSEIF:
                case Token::T_FOREACH:
                case Token::T_FOR:
                case Token::T_WHILE:
                case Token::T_DO:
                case Token::T_BOOLEAN_AND:
                case Token::T_LOGICAL_AND:
                case Token::T_BOOLEAN_OR:
                case Token::T_LOGICAL_OR:
                case Token::T_SPACESHIP:
                case Token::T_CASE:
                case Token::T_DEFAULT:
                case Token::T_CATCH:
                case Token::T_CONTINUE:
                    $ccn++;
                    break;
                case Token::T_TERNARY:
                    $ccn = $ccn + 2;
                    break;
                case Token::T_COALESCE:
                    $ccn = $ccn + 2;
                    break;
            }

        }

        $info->setCyclomaticComplexityNumber(max(1, $ccn));
        return $info;
    }
}
