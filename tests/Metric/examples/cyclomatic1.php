<?php
class A {
    public function foo1()
    {
        if(true) {
            if(false) {

            }
        }
    }

    public function foo2()
    {
        if(true) {

        }
    }

    public function foo3()
    {
    }
}

class B {
    public function foo()
    {
        if(true) {

        }

        foreach(array() as $foo) {
            if(false) {
                continue;
            }
        }
    }
}