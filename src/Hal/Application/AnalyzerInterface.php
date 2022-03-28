<?php
declare(strict_types=1);

namespace Hal\Application;

use Hal\Metric\Metrics;

/**
 * Responsible for the analysis of a project, with rules and workflows depending on the implementation of this
 * interface.
 */
interface AnalyzerInterface
{
    /**
     * Executes the analysis on all files that matches the configuration requests, using the configuration rules.
     *
     * @return Metrics
     */
    public function process(): Metrics;
}
