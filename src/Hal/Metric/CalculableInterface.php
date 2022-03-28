<?php
declare(strict_types=1);

namespace Hal\Metric;

/**
 * Each implementation of this interface must provide a calculation method that will add information on the metrics.
 */
interface CalculableInterface
{
    /**
     * Reads the metrics to infer deductions to other metrics.
     * Example: read all classes to infer the afferent and efferent coupling.
     *
     * @return void
     */
    public function calculate(): void;
}
