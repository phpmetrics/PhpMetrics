<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Reflected;


class Klass
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var Method[]
     */
    private $methods = array();

    /**
     * @var array
     */
    private $attributes = array();

    /**
     * @var array
     */
    private $parents = array();

    /**
     * @var boolean
     */
    private $isAbstract = false;

    /**
     * @var \SplFixedArray
     */
    private $tokens;

    /**
     * Klass constructor.
     * @param string $name
     * @param string $namespace
     */
    public function __construct($name = null, $namespace = null)
    {
        $this->name = $name;
        $this->namespace = $namespace;
        $this->tokens = new \SplFixedArray(0);
    }


    /**
     * @param bool|true $unique
     * @return array
     */
    public function getDependencies($unique = true)
    {
        $dependencies = array();
        foreach($this->methods as $method) {
            $dependencies = array_merge($dependencies, $method->getDependencies());
        }

        if($unique) {
            $dependencies = array_unique($dependencies);
        }

        return $dependencies;
    }
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     * @return Klass
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return sprintf('%s\\%s', rtrim($this->namespace, '\\'), $this->name);
    }

    /**
     * @return Method[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param Method[] $methods
     * @return Klass
     */
    public function setMethods($methods)
    {
        $this->methods = $methods;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return Klass
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAbstract()
    {
        return $this->isAbstract;
    }

    /**
     * @param boolean $isAbstract
     * @return $this
     */
    public function setIsAbstract($isAbstract)
    {
        $this->isAbstract = (bool)$isAbstract;
        return $this;
    }

    /**
     * @return bool
     */
    public function isInterface()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isAnonymous()
    {
        return false;
    }

    /**
     * @return array
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * @param array $parents
     * @return Klass
     */
    public function setParents(array $parents)
    {
        $this->parents = $parents;
        return $this;
    }

    /**
     * @return \SplFixedArray
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @param array $tokens
     * @return Klass
     */
    public function setTokens(array $tokens)
    {
        $this->tokens = new \SplFixedArray(sizeof($tokens));
        $this->tokens->fromArray($tokens);
        return $this;
    }

}