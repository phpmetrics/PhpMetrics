<?php
class classA {
    private $a;
    private $b;

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
        $this->b = (string) $b;
        return $this;
    }

    public function foo() {
        return $this->a;
    }

    public function isGood() {
        return $this->a;
    }

    public function isTypedGood() {
        return (bool) $this->a;
    }

    public function hasA() {
        return $this->a;
    }
}