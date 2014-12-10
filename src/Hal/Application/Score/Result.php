<?php

/*
* (c) Jean-François Lépine <https://twitter.com/Halleck45>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Hal\Application\Score;

use Hal\Component\Bounds\BoundsInterface;
use Hal\Component\Bounds\Result\ResultInterface;
use Hal\Component\Result\ResultCollection;
use Hal\Component\Score\ScoreInterface;

/**
 * Score
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Result implements ScoreInterface{

    /**
     * @var array
     */
    private $scores = array();

    /**
     * @param $name
     * @param $score
     */
    public function push($name, $score) {
        $this->scores[$name] = $score;
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name) {
        return isset($this->scores[$name]);
    }

    /**
     * @param $name
     * @return float|null
     */
    public function get($name) {
        return $this->has($name) ? $this->scores[$name] : null;
    }

    /**
     * @return array
     */
    public function all() {
        return $this->scores;
    }
}