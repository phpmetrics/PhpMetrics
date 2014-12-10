<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Command\Job;
use Hal\Component\Score\ScoringInterface;
use Hal\Component\Result\ResultCollection;


/**
 * Job report renderer
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class CalculateScore implements JobInterface
{

    /**
     * @var ScoringInterface
     */
    private $scoring;

    /**
     * Constructor
     *
     * @param $scoring
     */
    public function __construct(ScoringInterface $scoring)
    {
        $this->scoring = $scoring;
    }


    /**
     * @inheritdoc
     */
    public function execute(ResultCollection $collection, ResultCollection $aggregatedResults) {
        $score = $this->scoring->calculate($collection, $aggregatedResults);
        $collection->setScore($score);
    }

}
