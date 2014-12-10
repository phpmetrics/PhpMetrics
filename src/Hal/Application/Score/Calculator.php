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



    public function highIsBetter($good, $bad, $note) {
        // we don't want to attribute score of 0. We'll use 90% bad
        $bad = $bad / 100 * 90;

        $good = $good - $bad;
        $note = $note - $bad;

        $score = (1-( $good / ($note + $good) )) * 100;

        $score = max(0, $score);
        $score = min (50, $score);
        return round($score, 2);
    }

    public function lowIsBetter($good, $bad, $note) {

        $limit = 50;
        // my formula isn't perfect. Do not hesitate to contribute
        $score = 1000/($good/($good+($bad-$note))*100/$good*$bad)*$limit/10;
        $score = max(0, $score);
        $score = min (50, $score);
        return round($score, 2);
    }
}