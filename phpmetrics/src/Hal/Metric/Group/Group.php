<?php

namespace Hal\Metric\Group;

use Hal\Metric\Metrics;

/**
 * Class Group
 * Allows to group metrics by regex, given them a name
 * @package Hal\Metric\Group
 */
class Group
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $regex;

    /**
     * Group constructor.
     * @param string $name
     * @param string $regex
     */
    public function __construct($name, $regex)
    {
        $this->name = $name;
        $this->regex = $regex;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * @param Metrics $metrics
     * @return Metrics
     */
    public function reduceMetrics(Metrics $metrics)
    {
        $all = $metrics->all();
        $matched = new Metrics();

        foreach ($all as $metric) {
            if (!preg_match($this->regex, $metric->getName())) {
                continue;
            }

            $matched->attach($metric);
        }

        return $matched;
    }
}
