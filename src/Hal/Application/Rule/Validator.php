<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Rule;


/**
 * Rule validator
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Validator {

    /**
     * Critical
     */
    const CRITICAL = 'critical';

    /**
     * Warning
     */
    const WARNING = 'warning';

    /**
     * Good
     */
    const GOOD = 'good';

    /**
     * unknown
     */
    const UNKNOWN = 'unknown';

    /**
     * @var RuleSet
     */
    private $ruleSet;

    /**
     * Constructor
     *
     * @param RuleSet $ruleSet
     */
    function __construct(RuleSet $ruleSet)
    {
        $this->ruleSet = $ruleSet;
    }

    /**
     * Validate score
     *
     * @param $key
     * @param $value
     * @return string
     */
    public function validate($key, $value) {
        $rule = $this->ruleSet->getRule($key);

        if(!is_array($rule) || !is_int($value)) {
            return self::UNKNOWN;
        }

        // according order
        if($rule[0] < $rule[2]) {
            // critical < warn < good
            switch(true) {
                case $value < $rule[1]:
                    return self::CRITICAL;
                case $value >= $rule[1] && $value < $rule[2]:
                    return self::WARNING;
                default:
                    return self::GOOD;
            }
        } else {
            // critical > warn > good
            switch(true) {
                case $value > $rule[1]:
                    return self::CRITICAL;
                case $value < $rule[2]:
                    return self::GOOD;
                default:
                    return self::WARNING;
            }
        }
    }

    /**
     * Get used ruleset
     *
     * @return RuleSet
     */
    public function getRuleSet() {
        return $this->ruleSet;
    }
}