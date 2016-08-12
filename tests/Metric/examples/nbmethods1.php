<?php

class A
{
    private $a;

    private $b;

    public function foo($a)
    {
        return $a + 1;
    }

    public function bar()
    {
        $this->a = null;
    }

    private function baz()
    {
        $this->b = $this->b * 3;
    }

    public function getA()
    {
        return $this->a;
    }

    public function setA($a)
    {
        $this->a = $a;
    }

    public function getB()
    {
        return $this->b;
    }

    public function setB($b)
    {
        $this->b = (string)$b;
        return $this;
    }

}