<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Mood\Abstractness;
use Hal\Component\Result\ResultCollection;


/**
 * Estimates Abstractness of package
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Abstractness {

    /**
     * Calculate abstractness
     *
     * @param ResultCollection $results Array of ResultSet
     * @return Result
     */
    public function calculate(ResultCollection $results) {

        $ac = $cc = $abstractness = 0;

        foreach($results as $result) {
            $rOOP = $result->getOOP();
            $cc += sizeof($rOOP->getConcreteClasses(), COUNT_NORMAL);
            $ac += sizeof($rOOP->getAbstractClasses(), COUNT_NORMAL);
        }

        $result = new Result;
        if($ac + $cc > 0) {
            $abstractness = round($ac / ($ac + $cc), 2);
        }
        $result->setAbstractness($abstractness);
        return $result;
    }

};