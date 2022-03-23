<?php

namespace Hal\Search;

use Hal\Metric\ClassMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metric;
use Hal\Metric\Registry;

class Search
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $config;

    /**
     * @param string $name
     * @param array $config
     */
    public function __construct($name, array $config)
    {
        $this->name = $name;
        $this->config = (object)$config;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function matches(Metric $metric)
    {
        $configAsArray = (array)$this->config;

        if (!empty($this->config->type)) {
            // Does the search concerns type ?
            if (!$this->matchExpectedType($metric, $this->config->type)) {
                return false;
            }
        }

        if (!empty($this->config->nameMatches)) {
            // Does the search concerns name ?
            if (!$this->matchExpectedName($metric, $this->config->nameMatches)) {
                return false;
            }
        }

        if (!empty($this->config->instanceOf)) {
            // Does the search is instance of given class or interface ?
            if (!$this->matchInstanceOf($metric, $this->config->instanceOf)) {
                return false;
            }
        }


        if (!empty($this->config->usesClasses)) {
            // Does the search use some classes
            if (!$this->usesClasses($metric, $this->config->usesClasses)) {
                return false;
            }
        }

        $registry = new Registry();
        foreach ($registry->allForStructures() as $metricName) {
            if (array_key_exists($metricName, $configAsArray)) {
                // Does the search use some structures (ccn, etc. )
                if (!$this->matchesMetric($metric, $metricName, $configAsArray[$metricName])) {
                    return false;
                }
            }
        }

        // should be latest in checks
        if (array_key_exists('failIfFound', $configAsArray)) {
            if (true === $this->config->failIfFound) {
                $metric->set('was-not-expected', true);
                if (!$metric->has('was-not-expected-by')) {
                    $metric->set('was-not-expected-by', []);
                }
                $bys = $metric->get('was-not-expected-by');
                $bys[] = $this->name;
                $metric->set('was-not-expected-by', $bys);
            }
        }

        return true;
    }

    private function matchExpectedType(Metric $metric, $expectedType)
    {
        switch ($expectedType) {
            case 'class':
                return $metric instanceof ClassMetric && !$metric instanceof InterfaceMetric;
            case 'interface':
                return $metric instanceof InterfaceMetric;
        }

        return false;
    }

    private function matchExpectedName(Metric $metric, $expectedName)
    {
        return preg_match('@' . $expectedName . '@i', $metric->getName());
    }

    private function matchInstanceOf(Metric $metric, $instanceOf)
    {
        foreach ($instanceOf as $expectedInterface) {
            $expectedInterface = ltrim($expectedInterface, '\\');
            if (!in_array($expectedInterface, (array)$metric->get('implements'))) {
                return false;
            }
        }

        return true;
    }

    private function usesClasses(Metric $metric, $usesClasses)
    {
        foreach ($usesClasses as $expectedClass) {
            foreach ((array)$metric->get('externals') as $use) {
                if (preg_match('@' . $expectedClass . '@i', $use)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function matchesMetric(Metric $metric, $metricName, $metricValue)
    {
        if (!preg_match_all('!^([=><]*)([\d\.]+)!', $metricValue, $matches, PREG_SET_ORDER)) {
            throw new \LogicException('Invalid search expression for key ' . $metricValue);
        }
        list(, $operator, $expected) = $matches[0];

        switch ($operator) {
            case '=':
            case '':
                return $metric->get($metricName) == $expected; // do not use === here
            case '>':
                return $metric->get($metricName) > $expected;
            case '<':
                return $metric->get($metricName) < $expected;
            case '>=':
            case '=>':
                return $metric->get($metricName) >= $expected;
            case '<=':
            case '=<':
                return $metric->get($metricName) <= $expected;
        }

        return false;
    }
}
