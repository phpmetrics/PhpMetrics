<?php
declare(strict_types=1);

namespace Hal\Application\Workflow\Task;

use Hal\Metric\CalculableInterface;
use Hal\Metric\CalculableWithFilesInterface;
use function array_map;

/**
 * This class is in charge of the task about processing the calculation of all calculable metrics.
 */
final class AnalyzerTask implements WorkflowTaskInterface
{
    /** @var array<int|string, CalculableInterface> */
    private readonly array $calculableAnalysis;

    /**
     * @param CalculableInterface ...$calculable
     */
    public function __construct(CalculableInterface ...$calculable)
    {
        $this->calculableAnalysis = $calculable;
    }

    /**
     * {@inheritDoc}
     */
    public function process(array $files): void
    {
        array_map(static function (CalculableInterface $calculable) use ($files): void {
            if ($calculable instanceof CalculableWithFilesInterface) {
                $calculable->setFiles($files);
            }
            $calculable->calculate();
        }, $this->calculableAnalysis);
    }
}
