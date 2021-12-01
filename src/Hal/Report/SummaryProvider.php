<?php

namespace Hal\Report;

use Hal\Application\Config\Config;
use Hal\Metric\Consolidated;
use Hal\Metric\Metrics;

abstract class SummaryProvider
{
    /**
     * @var int
     */
    protected $methodsByClass = 0;

    /**
     * @var int
     */
    protected $locByClass = 0;

    /**
     * @var int
     */
    protected $locByMethod = 0;

    /**
     * @var object
     */
    protected $sum;

    /**
     * @var object
     */
    protected $avg;

    /**
     * @var mixed
     */
    protected $treeInheritenceDepth;

    /**
     * @var Consolidated
     */
    protected $consolidated;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Metrics
     */
    protected $metrics;

    public function __construct(Metrics $metrics, Consolidated $consolidated, Config $config)
    {
        $this->consolidated = $consolidated;
        $this->config = $config;
        $this->metrics = $metrics;
        $this->sum = $consolidated->getSum();
        $this->avg = $consolidated->getAvg();

        // grouping results
        if ($this->sum->nbClasses > 0) {
            $this->methodsByClass = round($this->sum->nbMethods / $this->sum->nbClasses, 2);
            $this->locByClass = round($this->sum->lloc / $this->sum->nbClasses);
        }
        if ($this->sum->nbMethods > 0) {
            $this->locByMethod = round($this->sum->lloc / $this->sum->nbMethods);
        }

        $this->treeInheritenceDepth = $metrics->get('tree')->get('depthOfInheritanceTree');
    }

    abstract public function getReport();
}
