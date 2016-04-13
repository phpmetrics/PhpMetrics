<?php
namespace Hal\Component\Parser;


class Result
{

    /**
     * @var array
     */
    private $classes = array();

    /**
     * @var array
     */
    private $functions = array();

    /**
     * @return array
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * @param array $classes
     * @return Result
     */
    public function setClasses($classes)
    {
        $this->classes = $classes;
        return $this;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    /**
     * @param array $functions
     * @return Result
     */
    public function setFunctions($functions)
    {
        $this->functions = $functions;
        return $this;
    }
}