<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Reflected;


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
     * Map of aliases
     *
     * @var array
     */
    private $aliases = array();

    /**
     * @var array
     */
    private $tokens = array();

    /**
     * @var string
     */
    private $content;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = (string) $name;
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
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
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
     * @param $tokens
     * @return $this
     */
    public function setTokens($tokens)
    {
        $this->tokens = $tokens;
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
     * @param $mixed
     * @return $this
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
     * @return $this
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
     * @return $this
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
            $real = isset($this->aliases[$name]) ? $this->aliases[$name] : $name;
            array_push($dependencies, $real);
        }
        return array_unique($dependencies);
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