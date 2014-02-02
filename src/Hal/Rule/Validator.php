<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Rule;


/**
 * Rule validator
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Validator {

    /**
     * Critical
     */
    const CRITICAL = 0;

    /**
     * Warning
     */
    const WARNING = 1;

    /**
     * Good
     */
    const GOOD = 2;

    /**
     * unknown
     */
    const UNKNOWN = 3;

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
     * @return int
     */
    public function validate($key, $value) {
        $rule = $this->ruleSet->getRule($key);

        if(is_null($rule)) {
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
}