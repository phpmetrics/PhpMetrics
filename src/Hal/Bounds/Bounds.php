<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Bounds;
use Hal\Bounds\Result\BoundsResult;
use Hal\Result\ResultCollection;

/**
 * ResultBoundary
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Bounds implements BoundsInterface {

    /**
     * @inheritdoc
     */
    public function calculate(ResultCollection $collection) {
        $array = $collection->asArray();

        $arrayMerged = call_user_func_array('array_merge_recursive', $array);

        $min = $max = $average = $sum = array();
        foreach($arrayMerged as $key => $values) {
            $values = (array) $values;
            $max[$key] = max($values);
            $min[$key] = min($values);
            $sum[$key] = array_sum($values);
            $average[$key] = $sum[$key] / count($values, COUNT_NORMAL);
        }

        return new BoundsResult($min, $max, $average, $sum);
    }
}