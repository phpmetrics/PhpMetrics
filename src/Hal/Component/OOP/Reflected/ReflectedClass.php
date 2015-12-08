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
    protected $namespace;

    /**
     * @var string
     */
    protected $name;

    /**
     * Methods
     *
     * @var \SplObjectStorage[]
     */
    protected $methods;

    /**
     * Resolver for names
     *
     * @var NameResolver
     */
    protected $nameResolver;

    /**
     * Does the class is abstract ?
     *
     * @var bool
     */
    protected $isAbstract = false;

    /**
     * @var array
     */
    protected $interfaces = array();

    /**
     * Parent's name
     *
     * @var string
     */
    private $parent;

    /**
     * Constructor
     *
     * @param string $namespace
     * @param string $name
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
     * @param string $namespace
     * @return ReflectedClass
     */
    protected function setNamespace($namespace)
    {
        $this->namespace = (string) $namespace;
        return $this;
    }

    /**
     * @return \SplObjectStorage[]
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
            if(in_array($name, array('self', 'static'))) {
                $name = $this->getFullname();
            }
            $name = $this->nameResolver->resolve($name, $this->namespace);
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
     * @return string|null null when no parent class exists
     */
    public function getParent() {
        if ($this->parent === null) {
            return null;
        }
        return $this->nameResolver->resolve($this->parent, $this->namespace);
    }

    /**
     * @return array
     */
    public function getInterfaces()
    {
        $resolvedInterfaces = array();
        foreach($this->interfaces as $interface) {
            array_push($resolvedInterfaces, $this->nameResolver->resolve($interface, $this->namespace));
        }
        return $resolvedInterfaces;
    }

    /**
     * @param array $interfaces
     * @return $this
     */
    public function setInterfaces($interfaces)
    {
        $this->interfaces = $interfaces;
        return $this;
    }

    /**
     * Get anonymous classes contained in this class
     *
     * @return mixed
     */
    public function getAnonymousClasses() {
        $result = array();
        foreach($this->methods as $method) {
            $result = array_merge($result, $method->getAnonymousClasses());
        }
        return $result;
    }

};