<?php

class A {

    /**
     * This a long comment
     *
     * @return int
     */
    public function x()
    {
        // any comment
        // another comment
        $x = 1 + 1;
        $x = 1 + 1;
        $x = 1 + 1;
        return $x;
    }
}

/**
 * Class B
 * @package Foo
 */
class B {

    public function x()
    {
        $x = 1 + 1;
        return $x;
    }
}