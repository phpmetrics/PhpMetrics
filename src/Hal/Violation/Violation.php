<?php
declare(strict_types=1);

namespace Hal\Violation;

use Hal\Metric\Metric;

/**
 * Defines a design for what is called a violation. It has a name, a level, and a description.
 * The application of the violation means the given metric will be analysed against some rules, and if passes,
 * the violation is triggered.
 *
 * TODO: const should be Enum now.
 * TODO: use PHP Attributes to set names and levels to the violations.
 */
interface Violation
{
    public const int INFO = 0;
    public const int WARNING = 1;
    public const int ERROR = 2;
    public const int CRITICAL = 3;

    /**
     * Returns the name of the violation.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Apply the violation against the given metric.
     *
     * @param Metric $metric
     * @return void
     */
    public function apply(Metric $metric): void;

    /**
     * Returns the level of the violation.
     *
     * @return int
     */
    public function getLevel(): int;

    /**
     * Returns the description of the violation.
     *
     * @return string
     */
    public function getDescription(): string;
}
