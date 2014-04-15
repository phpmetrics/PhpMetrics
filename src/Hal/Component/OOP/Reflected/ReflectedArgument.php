<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Reflected;


/**
 * Result (argument)
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ReflectedArgument {

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $isRequired;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $type
     * @param bool $isRequired
     */
    public function __construct($name, $type, $isRequired = false)
    {
        $this->name = (string) $name;
        $this->isRequired = (bool) $isRequired;
        $this->type = (string) $type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isRequired() {
        return $this->isRequired;
    }
};