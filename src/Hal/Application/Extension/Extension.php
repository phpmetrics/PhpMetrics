<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Extension;


use Hal\Application\Config\Configuration;
use Hal\Component\Bounds\Bounds;
use Hal\Component\Result\ResultCollection;

interface Extension {

    /**
     * @param Configuration $configuration
     * @param ResultCollection $collection
     * @param ResultCollection $aggregatedResults
     * @param Bounds $bounds
     * @return mixed
     */
    public function receive(Configuration $configuration, ResultCollection $collection, ResultCollection $aggregatedResults, Bounds $bounds);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return ReporterHtmlSummary
     */
    public function getReporterHtmlSummary();

    /**
     * @return Reporter
     */
    public function getReporterCliSummary();

}