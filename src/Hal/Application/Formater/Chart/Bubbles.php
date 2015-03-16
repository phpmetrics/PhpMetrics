<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Formater\Chart;
use Hal\Application\Formater\FormaterInterface;
use Hal\Application\Rule\Validator;
use Hal\Component\Bounds\BoundsInterface;
use Hal\Component\Chart\Graphviz;
use Hal\Component\Result\ResultCollection;


/**
 * Bubbles generator
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Bubbles implements FormaterInterface {

    /**
     * Bounds
     *
     * @var BoundsInterface
     */
    private $bound;

    /**
     * Validator
     *
     * @var Validator
     */
    private $validator;

    /**
     * Constructor
     *
     * @param Validator $validator
     * @param BoundsInterface $bound
     */
    public function __construct(Validator $validator, BoundsInterface $bound)
    {
        $this->bound = $bound;
        $this->validator = $validator;
    }

    /**
     * @inheritdoc
     */
    public function terminate(ResultCollection $collection, ResultCollection $groupedResults){

        $text = 'digraph G {'
            . PHP_EOL .'bgcolor=white;'
            . PHP_EOL .'node [shape=circle, color=lightblue2, style=filled];';

        $width = 300;

        foreach($collection as $item) {

            // color
            $valid = $this->validator->validate('maintainabilityIndex', $item->getMaintainabilityIndex()->getMaintainabilityIndex());
            switch($valid) {
                case Validator::CRITICAL:   $color = 'red'; break;
                case Validator::GOOD:       $color = 'chartreuse4'; break;
                case Validator::WARNING:    $color = 'gold1'; break;
                case Validator::UNKNOWN:    $color = 'grey'; break;
            }

            // size
            $size = round($item->getMcCabe()->getCyclomaticComplexityNumber() * $width / 100);

            $text .= PHP_EOL. sprintf('"%1$s" [color=%2$s, tooltip="%3$s", width=%4$s, height=%4$s, label=""];'
                , $item->getName()
                , $color
                , $item->getName()
                , $size
                , $size
            );
        }

        $text .= PHP_EOL.'}';

        $chart = new Graphviz();
        return $chart->getImage($text);
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return 'Bubbles chart';
    }
}