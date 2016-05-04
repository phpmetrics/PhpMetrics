<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Reflected;


class Call
{

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @var bool
     */
    private $isItself = false;

    /**
     * @var bool
     */
    private $isStatic = false;

    /**
     * @var bool
     */
    private $isParent = false;

    /**
     * Call constructor.
     * @param string $type
     * @param string $methodName
     */
    public function __construct($type, $methodName)
    {
        $this->type = $type;
        $this->methodName = $methodName;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @return boolean
     */
    public function isItself()
    {
        return $this->isItself;
    }

    /**
     * @param boolean $isItself
     */
    public function setIsItself($isItself)
    {
        $this->isItself = (bool)$isItself;
    }

    /**
     * @return boolean
     */
    public function isStatic()
    {
        return $this->isStatic;
    }

    /**
     * @param boolean $isStatic
     */
    public function setIsStatic($isStatic)
    {
        $this->isStatic = (bool)$isStatic;
    }

    /**
     * @return boolean
     */
    public function isParent()
    {
        return $this->isParent;
    }

    /**
     * @param boolean $isParent
     * @return Call
     */
    public function setIsParent($isParent)
    {
        $this->isParent = (bool)$isParent;
        return $this;
    }


}