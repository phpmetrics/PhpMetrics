<?php
declare(strict_types=1);

namespace Hal\Report;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Metric\Consolidated;
use Hal\Metric\Metrics;
use Hal\Metric\ProjectMetric;
use stdClass;
use function round;

/**
 * This class calculates and holds all summary values of consolidated metrics, so they can be provided to any summary
 * writer object.
 */
abstract class SummaryProvider implements SummaryProviderInterface
{
    protected readonly float $methodsByClass;
    protected readonly float $locByClass;
    protected readonly float $locByMethod;
    protected readonly stdClass $sum;
    protected readonly stdClass $avg;
    protected readonly float $treeInheritanceDepth;
    protected readonly Metrics $metrics;
    protected readonly Consolidated $consolidated;

    public function __construct(protected readonly ConfigBagInterface $config)
    {
    }

    /**
     * {@inheritDoc}
     */
    final public function summarize(Metrics $metrics): void
    {
        $this->metrics = $metrics;
        $this->consolidated = new Consolidated($metrics);
        $sum = $this->consolidated->getSum();
        $this->sum = $sum;
        $this->avg = $this->consolidated->getAvg();

        // grouping results
        $this->methodsByClass = ($sum->nbClasses > 0) ? round($sum->nbMethods / $sum->nbClasses, 2) : 0;
        $this->locByClass = ($sum->nbClasses > 0) ? round($sum->lloc / $sum->nbClasses, 2) : 0;
        $this->locByMethod = ($sum->nbMethods > 0) ? round($sum->lloc / $sum->nbMethods, 2) : 0;

        /** @var ProjectMetric $wholeGraph */
        $wholeGraph = $metrics->get('tree');
        $this->treeInheritanceDepth = $wholeGraph->get('depthOfInheritanceTree');
    }
}
