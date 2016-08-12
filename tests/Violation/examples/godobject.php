<?php
class IAmBlob {
    private $a;
    private $b;
    private $c;
    private $d;

    public function flow1_A()
    {
        $this->a = 1;
    }

    public function flow1_B()
    {
        return $this->a * 3 + $this->b;
    }

    public function flow1_C()
    {
        $this->b = 1;
    }

    public function flow2_A()
    {
        $this->c = 1;
        $a = new A;
        $b = new B;
    }

    public function flow3_A()
    {
        $this->d = 1;
        $a = new C;
        $b = new D;
    }

    public function flow3_B()
    {
        return $this->d  * 3;
    }

    public function flow3_C()
    {
        return $this->d  * 4;
        $a = new E;
        $b = new F;
    }

    public function flow3_D()
    {
        return $this->d  * 5;
    }

    public function flow3_E()
    {
        return $this->d  * 6;
    }
}

class IAmGood {
    public function foo($x)
    {
        return $x * 3;
    }
}