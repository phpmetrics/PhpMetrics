<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Structural\CardAndAgresti;

use Hal\Component\OOP\Reflected\ReflectedClass;

/**
 * Calculates Card And Agresti metric
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class SystemComplexity {

    /**
     * Calculates Card And Agresti metric
     *
     *      Fan-out = Structural fan-out = Number of other procedures this procedure calls
     *      v = number of input/output variables for a procedure
     *
     *      (SC) Structural complexity = fan-out^2
     *      (DC) Data complexity = v / (fan-out + 1)
     *
     * @param ReflectedClass $class
     * @return Result
     */
    public function calculate(ReflectedClass $class)
    {
        $result = new Result;


        $sy = $dc = $sc = array(); // in system
        foreach($class->getMethods() as $method) {
            $fanout = sizeof($method->getDependencies(), COUNT_NORMAL);
            $v = sizeof($method->getArguments(), COUNT_NORMAL) + sizeof($method->getReturns(), COUNT_NORMAL);

            $ldc = $v / ($fanout + 1);
            $lsc = pow($fanout, 2);
            $sy[] = $ldc + $lsc;
            $dc[] = $ldc;
            $sc[] = $lsc;

        }
        $result
            ->setRelativeStructuralComplexity(empty($sc) ? 0 : round(array_sum($sc) / sizeof($sc, COUNT_NORMAL), 2))
            ->setRelativeDataComplexity(empty($dc) ? 0 : round(array_sum($dc) / sizeof($dc, COUNT_NORMAL), 2))
            ->setRelativeSystemComplexity(empty($sy) ? 0 : round(array_sum($sy) / sizeof($sy, COUNT_NORMAL), 2))
            ->setTotalStructuralComplexity(round(array_sum($sc), 2))
            ->setTotalDataComplexity(round(array_sum($dc), 2))
            ->setTotalSystemComplexity(round(array_sum($dc) + array_sum($sc), 2))
        ;

        return $result;
    }
}