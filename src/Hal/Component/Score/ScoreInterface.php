<?php

/*
* (c) Jean-François Lépine <https://twitter.com/Halleck45>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Hal\Component\Score;

/**
 * Score
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
interface ScoreInterface {

    /**
     * @param $name
     * @param $score
     */
    public function push($name, $score);

    /**
     * @param $name
     * @return bool
     */
    public function has($name);

    /**
     * @param $name
     * @return float|null
     */
    public function get($name);

    /**
     * @return array
     */
    public function all();
}