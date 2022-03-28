<?php
declare(strict_types=1);

namespace Hal\Application\Workflow;

use Hal\Metric\Metrics;

/**
 * Defines the rules about how a workflow must be executed.
 */
interface WorkflowHandlerInterface
{
    /**
     * Execute the workflow on the given files. Each task in the workflow will receive the files list and be processed
     * to update the Metrics object, shared across all tasks.
     * Once the workflow finishes, the final updated metrics is returned.
     *
     * @param array<int, string> $files
     * @return Metrics
     */
    public function execute(array $files): Metrics;
}
