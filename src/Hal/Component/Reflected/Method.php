<?php
namespace Hal\Component\Reflected;


use Hal\Component\Parser\Helper\TypeResolver;
use Hal\Component\Token\Token;

class Method
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $visibility = Token::T_VISIBILITY_PUBLIC;

    /**
     * @var boolean
     */
    private $isStatic = false;

    /**
     * @var Argument[]
     */
    private $arguments = array();

    /**
     * @var array
     */
    private $tokens = array();

    /**
     * @var Call[]
     */
    private $calls = array();

    /**
     * @var ReturnedValue[]
     */
    private $returns = array();

    /**
     * @param bool|true $unique
     * @return array
     */
    public function getDependencies($unique = true)
    {
        $typeResolver = new TypeResolver();

        $dependencies = array();
        foreach($this->returns as $return) {
            if($typeResolver->isObject($return->getType())) {
                array_push($dependencies, $return->getType());
            }
        }
        foreach($this->arguments as $argument) {
            array_push($dependencies, $argument->getType());
        }
        foreach($this->calls as $call) {
            array_push($dependencies, $call->getType());
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
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param string $visibility
     * @return Method
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
        return $this;
    }

    public function isPublic()
    {
        return Token::T_VISIBILITY_PUBLIC === $this->getVisibility();
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
     * @return Method
     */
    public function setIsStatic($isStatic)
    {
        $this->isStatic = (bool)$isStatic;
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
     * @param array $tokens
     * @return Method
     */
    public function setTokens(array $tokens)
    {
        $this->tokens = $tokens;
        return $this;
    }

    public function getBody()
    {
        return implode(' ', $this->tokens);
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     * @return Method
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @return array
     */
    public function getCalls()
    {
        return $this->calls;
    }

    /**
     * @param array $calls
     * @return Method
     */
    public function setCalls(array $calls)
    {
        $this->calls = $calls;
        return $this;
    }

    /**
     * @return array
     */
    public function getReturns()
    {
        return $this->returns;
    }

    /**
     * @param array $returns
     * @return Method
     */
    public function setReturns(array $returns)
    {
        $this->returns = $returns;
        return $this;
    }

}