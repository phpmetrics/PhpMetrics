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
     * Constructor
     */
    public function __construct() {
        $this->ruleset = new RuleSet();
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

}