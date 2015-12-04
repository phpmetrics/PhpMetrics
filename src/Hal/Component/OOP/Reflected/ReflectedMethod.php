<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Reflected;
use Hal\Component\OOP\Reflected\ReflectedClass\ReflectedAnonymousClass;
use Hal\Component\OOP\Resolver\NameResolver;


/**
 * Result (method)
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ReflectedMethod {



    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $arguments = array();

    /**
     * @var array
     */
    private $returns = array();

    /**
     * @var array
     */
    private $internalCalls = array();

    /**
     * @var array
     */
    private $externalCalls = array();

    /**
     * @var array
     */
    private $dependencies = array();

    /**
     * Resolver for names
     *
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var array
     */
    private $tokens = array();

    /**
     * @var string
     */
    private $content;

    /**
     * Usage of method (getter, setter...)
     *
     * @var string
     */
    private $usage;

    /**
     * Anonymous class contained in this method
     *
     * @var array
     */
    private $anonymousClasses = array();

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = (string) $name;
        $this->nameResolver = new NameResolver();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \SplObjectStorage
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Attach argument
     *
     * @param ReflectedArgument $arg
     * @return $this
     */
    public function pushArgument(ReflectedArgument $arg) {
        array_push($this->arguments, $arg);
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @param \Hal\Component\Token\TokenCollection $tokens
     * @return self
     */
    public function setTokens($tokens)
    {
        $this->tokens = $tokens;
        return $this;
    }

    /**
     * Get the list of returned values
     *
     * @return array
     */
    public function getReturns() {
        return $this->returns;
    }

    /**
     * Attach ne return information
     *
     *      It make no sense for the moment to store any information abour return value / type. Maybe in PHP 6 ? :)
     *
     * @param string $mixed
     * @return self
     */
    public function pushReturn($mixed) {
        array_push($this->returns, $mixed);
        return $this;
    }

    /**
     * Get the list of calls
     *
     * @return array
     */
    public function getCalls() {
        return array_merge($this->internalCalls, $this->externalCalls);
    }

    /**
     * @return array
     */
    public function getExternalCalls()
    {
        return $this->externalCalls;
    }

    /**
     * @return array
     */
    public function getInternalCalls()
    {
        return $this->internalCalls;
    }

    /**
     * Attach new call
     *
     * @param $varname
     * @return self
     */
    public function pushCall($varname) {
        if(preg_match('!^$this!', $varname)) {
            array_push($this->internalCalls, $varname);
        } else {
            array_push($this->externalCalls, $varname);
        }

        return $this;
    }


    /**
     * Push dependency
     *
     * @param $name
     * @return self
     */
    public function pushDependency($name) {
        array_push($this->dependencies, $name);
        return $this;
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        // on read : compare with aliases. We cannot make it in pushDependency() => aliases aren't yet known
        $dependencies = array();
        foreach($this->dependencies as $name) {
            array_push($dependencies, $this->nameResolver->resolve($name, null));
        }
        return array_unique($dependencies);
    }

    /**
     * @param NameResolver $resolver
     * @return self
     */
    public function setNameResolver(NameResolver $resolver)
    {
        $this->nameResolver = $resolver;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSetter() {
        return MethodUsage::USAGE_SETTER == $this->getUsage();
    }

    /**
     * @return string
     */
    public function getUsage()
    {
        return $this->usage;
    }

    /**
     * @param string $usage
     */
    public function setUsage($usage)
    {
        $this->usage = $usage;
    }

    /**
     * @return bool
     */
    public function isGetter() {
        return MethodUsage::USAGE_GETTER == $this->getUsage();
    }

    /**
     * @param ReflectedAnonymousClass $class
     * @return $this
     */
    public function pushAnonymousClass(ReflectedAnonymousClass $class) {
        $this->anonymousClasses[] = $class;
        return $this;
    }

    /**
     * @return array
     */
    public function getAnonymousClasses()
    {
        return $this->anonymousClasses;
    }

};