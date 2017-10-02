<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metric;

/**
 * Trait MetricsVisitorTrait
 * Provide a generic way to ensure a metric visitor must be constructed with a metrics object.
 *
 * @package Hal\Metric
 */
trait MetricsVisitorTrait
{
    /** @var Metrics The Metrics object that will store all data analysis. */
    protected $metrics;

    /**
     * Constructor.
     * @param Metrics $metrics The Metrics object that will store all data analysis.
     */
    public function __construct(Metrics $metrics)
    {
        $this->metrics = $metrics;
    }
}
