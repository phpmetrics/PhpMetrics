<?php
namespace My;

class MyClass {
    public function foo()
    {
        return new static (
            $x, $z
        );
    }
}
