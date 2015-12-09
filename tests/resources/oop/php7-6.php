<?php
namespace My;
class Mother {

}

class A {

}


class B {
    public function foo():A
    {
        $c = new class extends Mother {
            public function any()
            {
                $d = new D;
            }
        }
    }
}


class C {
    public function foo()
    {
        $c = new class extends Mother {}
    }
}

class D {

}