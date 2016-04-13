<?php
namespace Hal\Component\Reflected;


class ReturnedValue
{
    const VOID = 'void';

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