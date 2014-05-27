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
            $rules = array(
                'cyclomaticComplexity' => array(10, 6, 2)
                , 'maintenabilityIndex' => array(0, 69, 85)
                , 'logicalLoc' => array(800, 400, 200)
                , 'volume' => array(1300, 1000, 300)
                , 'bugs' => array(0.35, 0.25, 0.15)
                , 'commentWeight' => array(36, 38, 41)
                , 'vocabulary' => array(51, 34, 27)
                , 'difficulty' => array(18, 15, 5.8)
                , 'instability' => array(1, .95, .45)
                , 'afferentCoupling' => array(20, 15, 9)
                , 'efferentCoupling' => array(15, 11, 7)
                , 'myerDistance' => array(10, 5, 2)
                , 'lcom' => array(3, 2, 1.5)
            );
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