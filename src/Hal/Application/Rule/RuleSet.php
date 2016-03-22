<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Rule;
use Hal\Component\Result\ExportableInterface;


/**
 * Rules
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class RuleSet implements ExportableInterface {

    /**
     * Rules
     *      for each rule:
     *      0 => CRITICAL limit
     *      1 => WARNING limit
     *      2 => GOOD limit
     *
     * @var array
     */
    private $rules;

    /**
     * Constructor
     *
     * @param array $rules
     */
    public function __construct(array $rules = array())
    {
        if(!$rules) {
            $default = new DefaultRuleSet();
            $rules = $default->asArray();
        }

        $this->rules = $rules;
    }

    /**
     * @inheritdoc
     */
    public function asArray() {
        return $this->rules;
    }

    /**
     * Get rule for key
     *
     * @param $key
     * @return array
     */
    public function getRule($key) {
        return isset($this->rules[$key]) ? $this->rules[$key] : null;
    }
}