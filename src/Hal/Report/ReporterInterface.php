<?php
namespace Hal\Report;

use Hal\Metric\Metrics;

interface ReporterInterface
{
    /**
     * Generates log reports on a predefined support (CLI, CSV, HTML, JSON, XML).
     * Generation can provide files or direct output.
     *
     * @param Metrics $metrics Given metrics where all required information to write logs are.
     * @return void
     */
    public function generate(Metrics $metrics);
}
