<?php
declare(strict_types=1);

namespace Hal\Metric;

/**
 * Each implementation of this interface must provide a calculation method that will add information on the metrics.
 * In addition with CalculableInterface, classes that implement this interface are requiring a list of files to process
 * their data and infer the expected metric.
 */
interface CalculableWithFilesInterface extends CalculableInterface
{
    /**
     * Set the files list that is required for this metric to be calculated.
     *
     * @param array<int, string> $files List of files to process
     * @return void
     */
    public function setFiles(array $files): void;
}
