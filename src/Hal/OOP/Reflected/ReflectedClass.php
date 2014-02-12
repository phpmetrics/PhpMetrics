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
     * Consolidated dependencies
     *
     * @var array array
     */
    private $dependencies = array();

    /**
     * Map of aliases
     *
     * @var array
     */
    private $aliases = array();

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
     * This method consolidated dependencies
     *
     * @param ReflectedMethod $method
     * @return $this
     */
    public function pushMethod(ReflectedMethod $method) {
        $this->methods->attach($method);

        foreach($method->getArguments() as $argument) {

            $name = $argument->getType();
            if(!in_array($argument->getType(), array($this->getName(), 'array'))) {
                $real = isset($this->aliases[$name]) ? $this->aliases[$name] : $name;
                array_push($this->dependencies, $real);
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @param array $aliases
     */
    public function setAliases(array $aliases)
    {
        $this->aliases = $aliases;
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }
};