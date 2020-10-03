<?php
namespace Hal\Violation;

/**
 * @package Hal\Violation
 *
 * @implements \IteratorAggregate<int,Violation>
 */
class Violations implements \IteratorAggregate, \Countable
{
    /**
     * @var Violation[]
     */
    private $data = [];

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * @param Violation $violation
     *
     * @return void
     */
    public function add(Violation $violation)
    {
        $this->data[] = clone $violation;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $string = '';
        foreach ($this->data as $violation) {
            $string .= $violation->getName() . ',';
        }
        return $string;
    }
}
