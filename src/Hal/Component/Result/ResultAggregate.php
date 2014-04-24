<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Result;


/**
 * Aggregate of ResultSet
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ResultAggregate implements ExportableInterface, ResultSetInterface {

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var \Hal\Metrics\Mood\Abstractness\Result
     */
    private $abstractness;

    /**
     * @var \Hal\Component\Bounds\Result\BoundsResult;
     */
    private $bounds;

    /**
     * @var \Hal\Metrics\Mood\Instability\Result
     */
    private $instability;

    /**
     * Constructor
     *
     * @param ResultCollection $collection
     */
    public function __construct($namespace) {
        $this->namespace = $namespace;
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return $this->namespace;
    }

    /**
     * Get depth of result
     *
     * @return int
     */
    public function getDepth() {
        return sizeof(preg_split('!([^\w]+)!', $this->getName()), COUNT_NORMAL);
    }

    /**
     * @inheritdoc
     */
    public function asArray() {
        return
            array_merge(array(
                'name' => $this->getName()
                )
                ,$this->getAbstractness() ? $this->getAbstractness()->asArray() : array()
                ,$this->getInstability() ? $this->getInstability()->asArray() : array()
                ,$this->getBounds() ? $this->getBounds()->asArray() : array()

        );
    }

    /**
     * @param \Hal\Metrics\Mood\Abstractness\Result $abstractness
     * @return ResultAggregate
     */
    public function setAbstractness(\Hal\Metrics\Mood\Abstractness\Result $abstractness)
    {
        $this->abstractness = $abstractness;
        return $this;
    }

    /**
     * @return \Hal\Metrics\Mood\Abstractness\Result
     */
    public function getAbstractness()
    {
        return $this->abstractness;
    }

    /**
     * @param \Hal\Component\Bounds\Result\BoundsResult $bounds
     * @return ResultAggregate
     */
    public function setBounds(\Hal\Component\Bounds\Result\BoundsResult $bounds)
    {
        $this->bounds = $bounds;
        return $this;
    }

    /**
     * @return \Hal\Component\Bounds\Result\BoundsResult
     */
    public function getBounds()
    {
        return $this->bounds;
    }

    /**
     * @param \Hal\Metrics\Mood\Instability\Result $instability
     * @return ResultAggregate
     */
    public function setInstability($instability)
    {
        $this->instability = $instability;
        return $this;
    }

    /**
     * @return \Hal\Metrics\Mood\Instability\Result
     */
    public function getInstability()
    {
        return $this->instability;
    }

}