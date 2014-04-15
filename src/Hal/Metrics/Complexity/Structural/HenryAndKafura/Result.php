<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Structural\HenryAndKafura;
use Hal\Component\Result\ExportableInterface;


/**
 * Represents coupling (for one class)
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Result implements ExportableInterface {

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $afferentCoupling = 0;

    /**
     * @var int
     */
    private $efferentCoupling = 0;

    /**
     * @var int
     */
    private $instability = 0;

    /**
     * Constructor
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = (string) $name;
    }

    /**
     * @inheritdoc
     */
    public function asArray()
    {
        return array(
            'instability' => $this->getInstability()
            , 'afferentCoupling' => $this->getAfferentCoupling()
            , 'efferentCoupling' => $this->getEfferentCoupling()
        );
    }


    /**
     * @param int $afferentCoupling
     */
    public function setAfferentCoupling($afferentCoupling)
    {
        $this->afferentCoupling = $afferentCoupling;
        return $this;
    }

    /**
     * @return int
     */
    public function getAfferentCoupling()
    {
        return $this->afferentCoupling;
    }

    /**
     * @param int $efferentCoupling
     */
    public function setEfferentCoupling($efferentCoupling)
    {
        $this->efferentCoupling = $efferentCoupling;
        return $this;
    }

    /**
     * @return int
     */
    public function getEfferentCoupling()
    {
        return $this->efferentCoupling;
    }

    /**
     * @param int $instability
     */
    public function setInstability($instability)
    {
        $this->instability = $instability;
        return $this;
    }

    /**
     * @return int
     */
    public function getInstability()
    {
        return $this->instability;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}