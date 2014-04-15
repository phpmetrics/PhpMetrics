<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Command\Job;
use Hal\Component\Result\ResultCollection;

/**
 * Template sequence
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Queue implements JobInterface
{

    /**
     * Jobs
     *
     * @var \SplQueue
     */
    private $jobs;

    /**
     * Construcor
     */
    public function __construct() {
        $this->jobs = new \SplQueue();
    }

    /**
     * Push in queue
     *
     * @param JobInterface $command
     * @return $this
     */
    public function push(JobInterface $command) {
        $this->jobs->push($command);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function execute(ResultCollection $collection) {
        foreach($this->jobs as $job) {
            $job->execute($collection);
        }
    }

}
