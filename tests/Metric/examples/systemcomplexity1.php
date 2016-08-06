<?php
class A {

    public function foo($a, $b, $c)
    {
        if(true) {
            return $c;
        } else {
            return $b;
        }

        (new B)->bar();
    }

}

class B {
    public function bar()
    {

    }
}