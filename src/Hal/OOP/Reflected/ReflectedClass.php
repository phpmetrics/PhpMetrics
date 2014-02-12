<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\OOP\Reflected;


/**
 * Result (class)
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ReflectedClass {

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $name;

    /**
     * Methods
     *
     * @var \SplObjectStorage
     */
    private $methods;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $namespace
     */
    public function __construct($namespace, $name)
    {
        $this->name = (string) $name;
        $this->namespace = (string) $namespace;
        $this->methods= new \SplObjectStorage();
    }

    /**
     * Get fullname (namespace + name)
     *
     * @return string
     */
    public function getFullname() {
        return $this->getNamespace().'\\'.$this->getName();
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
    public function getNamespace()
    {
        return rtrim($this->namespace, '\\');
    }

    /**
     * @return \SplObjectStorage
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Attach method
     *
     * @param ReflectedMethod $method
     * @return $this
     */
    public function pushMethod(ReflectedMethod $method) {
        $this->methods->attach($method);
        return $this;
    }

};