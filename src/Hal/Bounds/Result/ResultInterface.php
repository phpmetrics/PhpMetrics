<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Bounds\Result;


/**
 * ResultBoundary
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
interface ResultInterface {

    /**
     * Get average for
     *
     * @param $key
     * @return null
     */
    public function getAverage($key);

    /**
     * Get min for
     *
     * @param $key
     * @return null
     */
    public function getMin($key);

    /**
     * Get max for
     *
     * @param $key
     * @return null
     */
    public function getMax($key);

    /**
     * Get sum for
     *
     * @param $key
     * @return null
     */
    public function getSum($key);

    /**
     * Get any
     *
     * @param string $type
     * @param string $key
     * @return null
     */
    public function get($type, $key);
}