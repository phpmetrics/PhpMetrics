<?php
declare(strict_types=1);

namespace Hal\Application\Workflow\Task;

/**
 * This interface represents a single task in a workflow that is composed of a succession of tasks to be processed in a
 * particular order. Each task of this workflow must proceed a list of files.
 */
interface WorkflowTaskInterface
{
    /**
     * Processes a list of file to make them parsed on a single simple step.
     * The aim of the step belongs to the step itself.
     *
     * All tasks together are organized in a workflow.
     *
     * @param array<int, string> $files List of files to process.
     * @return void
     */
    public function process(array $files): void;
}
