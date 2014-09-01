<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Evaluation;
use Hal\Component\Bounds\BoundsInterface;
use Hal\Component\Result\ResultCollection;

/**
 * Evaluates rule and provides Evaluation object
 *
 * Proxy of \Hoa\Ruler\Ruler
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Evaluator
{

    /**
     * @var ResultCollection
     */
    private $collection;

    /**
     * @var ResultCollection
     */
    private $aggregatedResults;

    /**
     * @var BoundsInterface
     */
    private $bound;

    /**
     * Constructor
     *
     * @param ResultCollection $collection
     * @param ResultCollection $aggregatedResults
     * @param \Hal\Component\Bounds\BoundsInterface $bound
     */
    public function __construct(ResultCollection $collection, ResultCollection $aggregatedResults, BoundsInterface $bound)
    {
        $this->aggregatedResults = $aggregatedResults;
        $this->collection = $collection;
        $this->bound = $bound;
    }

    /**
     * Evaluate rule
     *
     * @param string $rule
     * @throws \LogicException
     * @return Evaluation
     */
    public function evaluate($rule) {
        $result = new Evaluation;

        if(0 == strlen($rule) ||is_null($rule)) {
            return $result;
        }

        $bounds = $this->bound->calculate($this->collection);
        $ruler = new \Hoa\Ruler\Ruler();
        $context = new \Hoa\Ruler\Context();

        // general
        foreach($bounds->asArray() as $category => $values) {
            $context[$category] = (object) $values;
        }

        // by package
        foreach($this->aggregatedResults as $aggregate) {
            $array = $aggregate->asArray();
            $c = array();
            foreach($array as $k => $v) {
                if(is_array($v)) {
                    $v = (object) $v;
                }
                $c[$k] = $v;
            }
            $context[$aggregate->getName()] = (object) $c;
        }


        try {
            $result->setValid(true === $ruler->assert($rule, $context));
        } catch(\Hoa\Ruler\Exception\Asserter $e) {
            throw new \LogicException(sprintf('Cannot evaluate rule : %s', $e->getMessage()));
        }
        return $result;
    }
}