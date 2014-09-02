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
     * Resolver for names
     *
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * Does the class is abstract ?
     *
     * @var bool
     */
    private $isAbstract = false;

    /**
     * Parent's name
     *
     * @var string
     */
    private $parent;

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
        $this->methods = array();
        $this->nameResolver = new NameResolver();
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
        $this->methods[$method->getName()] = $method;
        return $this;
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        $dependencies = array();
        foreach($this->getMethods() as $method) {
            $dependencies = array_merge($dependencies, $method->getDependencies());
        }
        foreach($dependencies as &$name) {
//            $name = preg_replace('!^(\\\\)!', '', $name);
            $name = $this->nameResolver->resolve($name, $this->getNamespace());
        }
        return array_unique($dependencies);
    }

    /**
     * @param NameResolver $resolver
     * @return $this
     */
    public function setNameResolver(NameResolver $resolver)
    {
        $this->nameResolver = $resolver;
        return $this;
    }

    /**
     * Set abstractness of method
     *
     * @param boolean $bool
     * @return $this
     */
    public function setAbstract($bool) {
        $this->isAbstract = (bool) $bool;
        return $this;
    }

    /**
     * Is Abstract ?
     *
     * @return bool
     */
    public function isAbstract() {
        return $this->isAbstract;
    }

    /**
     * Set the parent name
     *
     * @param $parent
     * @return $this
     */
    public function setParent($parent) {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get the parent name
     *
     * @return string
     */
    public function getParent() {
        return $this->nameResolver->resolve($this->parent, $this->getNamespace());
    }
};