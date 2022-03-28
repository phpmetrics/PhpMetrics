<?php
declare(strict_types=1);

namespace Hal\Application\Workflow;

use Hal\Application\Workflow\Task\WorkflowTaskInterface;
use Hal\Component\Output\Output;
use Hal\Metric\Metrics;

/**
 * Handles all the tasks to process to execute the workflow.
 */
final class WorkflowHandler implements WorkflowHandlerInterface
{
    public function __construct(
        private readonly Metrics $metrics,
        private readonly WorkflowTaskInterface $parserTask,
        private readonly WorkflowTaskInterface $analyzeTask,
        private readonly Output $output
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(array $files): Metrics
    {
        $this->output->writeln('Parsing all files...');
        $this->parserTask->process($files);
        $this->output->writeln('Executing system analyzes...');
        $this->analyzeTask->process($files);
        return $this->metrics;
    }
}
