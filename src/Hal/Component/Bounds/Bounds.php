<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Bounds;
use Hal\Component\Bounds\Result\BoundsResult;
use Hal\Component\Result\ResultCollection;

/**
 * ResultBoundary
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Bounds implements BoundsInterface {

    /**
     * @inheritdoc
     * @return \Hal\Component\Bounds\Result\ResultInterface
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
            $average[$key] = round($sum[$key] / count($values, COUNT_NORMAL),2);
        }

        return new BoundsResult($min, $max, $average, $sum);
    }
}