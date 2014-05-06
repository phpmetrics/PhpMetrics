<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Formater\Details;
use Hal\Component\Bounds\Bounds;
use Hal\Component\Bounds\BoundsAgregateInterface;
use Hal\Component\Bounds\BoundsInterface;
use Hal\Component\Bounds\Result\ResultInterface;
use Hal\Application\Formater\FormaterInterface;
use Hal\Component\Result\ResultCollection;
use Hal\Application\Rule\Validator;


/**
 * Formater for xml export
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Csv implements FormaterInterface {

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * @inheritdoc
     */
    public function terminate(ResultCollection $collection, ResultCollection $groupedResults){

        $fwd = fopen('php://memory', 'w');
        if(sizeof($collection, COUNT_NORMAL) > 0) {
            $r = current($collection->asArray());
            $labels = array_keys($r);
            fputcsv($fwd, $labels);
        }
        foreach($collection as $item) {
            fputcsv($fwd, $item->asArray());
        }

        rewind($fwd);
        $r =  stream_get_contents($fwd);
        fclose($fwd);
        return $r;
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return 'CSV';
    }
}