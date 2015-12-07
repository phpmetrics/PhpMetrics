<?php
namespace My;
class Main {
    public function foo()
    {
        $c = new class extends Another {
            public function foo1()
            {

            }
            public function foo2()
            {

            }
            public function foo3()
            {

            }
        };
    }
}