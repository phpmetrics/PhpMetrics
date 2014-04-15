<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Rule;


/**
 * Rules
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class RuleSet {

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
            $rules = array(
                'cyclomaticComplexity' => array(50, 20, 10)
            , 'maintenabilityIndex' => array(0, 65, 85)
            , 'logicalLoc' => array(800, 400, 200)
            , 'volume' => array(1000, 800, 400)
            , 'bugs' => array(2, 2, 1)
            , 'commentWeight' => array(0, 10, 20)
            , 'vocabulary' => array(30, 26, 25)
            , 'difficulty' => array(14, 11, 7)
            , 'instability' => array(1, .95, .45)
            , 'afferentCoupling' => array(20, 15, 9)
            , 'efferentCoupling' => array(15, 11, 7)
            );
        }

        $this->rules = $rules;
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