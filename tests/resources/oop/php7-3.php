<?php
namespace My;
class MainClass {
    public function foo() {

        $x = new class {
            public function sub() {}
        };
    }

    public function bar()
    {

    }
}
