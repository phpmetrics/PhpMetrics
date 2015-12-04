<?php
namespace My;
class MainClass {
    public function foo() {
        $x = new class {};
        $x = new class {};
    }

    public function bar()
    {
        $y = new class extends \StdClass {};
    }
}
