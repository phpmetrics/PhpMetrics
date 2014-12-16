<?php

/*
* (c) Jean-François Lépine <https://twitter.com/Halleck45>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Hal\Application\Score;

use Hal\Component\Bounds\BoundsInterface;
use Hal\Component\Result\ResultCollection;

/**
 * Sub component for formulas
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Calculator {

    /**
     * @var int
     */
    private $limit = Scoring::MAX;

    /**
     * @param $good
     * @param $bad
     * @param $note
     * @return float
     */
    public function highIsBetter($good, $bad, $note) {

        $score = (($note - $bad) / ($good - $bad)) * $this->limit;
        $score = max(0, $score);
        $score = min ($this->limit, $score);
        return round($score, 2);
    }

    /**
     * @param $good
     * @param $bad
     * @param $note
     * @return float
     */
    public function lowIsBetter($good, $bad, $note) {

        $score = $this->limit - ($note - $good) / ($bad - $good) * $this->limit;
        $score = max(0, $score);
        $score = min ($this->limit, $score);
        return round($score, 2);
    }
}