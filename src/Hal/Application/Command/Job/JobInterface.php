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
 * Options structure
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
interface JobInterface
{

    /**
     * Execute job
     *
     * @param ResultCollection $collection
     * @param ResultCollection $aggregatedResults
     * @return void
     */
    public function execute(ResultCollection $collection, ResultCollection $aggregatedResults);

}
