<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config;
use Hal\Application\Rule\RuleSet;
use Hal\Component\Config\ConfigurationInterface;

/**
 * Represents the configuration for analysis
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * RuleSet
     *
     * @var RuleSet
     */
    private $ruleset;

    /**
     * @var PathConfiguration
     */
    private $path;

    /**
     * Condition of failure
     *
     * @var string
     */
    private $failureCondition;

    /**
     * Targets of logging
     *
     * @var LoggingConfiguration
     */
    private $logging = array();

    /**
     * @var TemplateConfiguration
     */
    private $template;

    /**
     * @var bool
     */
    private $ignoreErrors = false;

    /**
     * Constructor
     */
    public function __construct() {
        $this->ruleset = new RuleSet();
        $this->failureCondition = null;
        $this->path = new PathConfiguration();
        $this->logging = new LoggingConfiguration();
        $this->template = new TemplateConfiguration();
    }

    /**
     * @param \Hal\Application\Rule\RuleSet $ruleset
     * @return self
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
     * @return self
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
     * @param LoggingConfiguration $logging
     * @return self
     */
    public function setLogging(LoggingConfiguration $logging)
    {
        $this->logging = $logging;
        return $this;
    }

    /**
     * @return LoggingConfiguration
     */
    public function getLogging()
    {
        return $this->logging;
    }

    /**
     * @param PathConfiguration $path
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return PathConfiguration
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param TemplateConfiguration $template
     * @return self
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return TemplateConfiguration
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return boolean
     */
    public function getIgnoreErrors()
    {
        return $this->ignoreErrors;
    }

    /**
     * @param boolean $ignoreErrors
     * @return $this
     */
    public function setIgnoreErrors($ignoreErrors)
    {
        $this->ignoreErrors = $ignoreErrors;
        return $this;
    }


}