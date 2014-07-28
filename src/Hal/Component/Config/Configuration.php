<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Config;
use Hal\Application\Rule\RuleSet;

/**
 * Represents the configuration for analysis
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Configuration
{
    /**
     * RuleSet
     *
     * @var RuleSet
     */
    private $ruleset;

    /**
     * Directories to exclude (regex)
     *
     * @var string
     */
    private $excludeDirs;

    /**
     * Extensions to include (regex)
     *
     * @var string
     */
    private $extensions;

    /**
     * Condition of failure
     *
     * @var string
     */
    private $failureCondition;

    /**
     * Constructor
     */
    public function __construct() {
        $this->ruleset = new RuleSet();
        $this->failureCondition = null;
    }

    /**
     * @param \Hal\Application\Rule\RuleSet $ruleset
     * @return $this;
     */
    public function setRuleSet(RuleSet $ruleset)
    {
        $this->ruleset = $ruleset;
        return $this;
    }

    /**
     * @return \Hal\Application\Rule\RuleSet
     */
    public function getRuleSet()
    {
        return $this->ruleset;
    }

    /**
     * @param string $failureCondition
     * @return $this
     */
    public function setFailureCondition($failureCondition)
    {
        $this->failureCondition = $failureCondition;
        return $this;
    }

    /**
     * @return string
     */
    public function getFailureCondition()
    {
        return $this->failureCondition;
    }

    /**
     * @param string $exclude
     * @return $this
     */
    public function setExcludeDirs($exclude)
    {
        $this->excludeDirs = $exclude;
        return $this;
    }

    /**
     * @return string
     */
    public function getExcludeDirs()
    {
        return $this->excludeDirs;
    }

    /**
     * @param string $extensions
     * @return $this
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
        return $this;
    }

    /**
     * @return string
     */
    public function getExtensions()
    {
        return $this->extensions;
    }


}