<?php
namespace Hal\Component\Reflected;


class ReturnedValue
{
    /**
     * @var string
     */
    private $type;

    /**
     * ReturnedValue constructor.
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}