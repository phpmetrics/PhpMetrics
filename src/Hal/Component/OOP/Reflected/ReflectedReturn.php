<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Reflected;
use Hal\Component\OOP\Resolver\NameResolver;


/**
 * represents the value returned by a method
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ReflectedReturn {

    const STRICT_TYPE_HINT = 1;
    const DOC_TYPE_HINT = 2;
    const ESTIMATED_TYPE_HINT = 3;
    const VALUE_UNKNOW = 'unknown';

    /**
     * @var string
     */
    private $value = self::VALUE_UNKNOW;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $mode = self::ESTIMATED_TYPE_HINT;

    /**
     * @param null $type
     * @param string $value
     * @param int $mode
     */
    public function __construct($type = null, $value = self::VALUE_UNKNOW, $mode = self::ESTIMATED_TYPE_HINT)
    {
        $this->type = $type;
        $this->value = $value;
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return ReflectedReturn
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ReflectedReturn
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param int $mode
     * @return ReflectedReturn
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

};