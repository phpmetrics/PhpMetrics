<?php
declare(strict_types=1);

namespace Hal\Application;

use Hal\Application\Workflow\WorkflowHandlerInterface;
use Hal\Component\File\FinderInterface;
use Hal\Metric\Metrics;
use Hal\Violation\ViolationParserInterface;

/**
 * Main class of the default PhpMetrics application. Request for the analysis of all files found by the finder according
 * to the list of paths, and requests for the violations to be applied against the metrics given by the workflow
 * execution.
 */
final class Analyzer implements AnalyzerInterface
{
    /**
     * @param array<string> $pathsList
     * @param FinderInterface $finder
     * @param WorkflowHandlerInterface $workflowHandler
     * @param ViolationParserInterface $violationParser
     */
    public function __construct(
        private readonly array $pathsList,
        private readonly FinderInterface $finder,
        private readonly WorkflowHandlerInterface $workflowHandler,
        private readonly ViolationParserInterface $violationParser
    ) {
    }

    /**
     * Executes the analysis on all files that matches the configuration requests, using the configuration rules.
     *
     * @return Metrics
     */
    public function process(): Metrics
    {
        $files = $this->finder->fetch($this->pathsList);
        $metrics = $this->workflowHandler->execute($files);
        $this->violationParser->apply($metrics);

        return $metrics;
    }
}
