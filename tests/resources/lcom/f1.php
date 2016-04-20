<?php

// A -> B -> x
// C -> y <- D -> E

class Foo {

    private $x;
    private $y;

    public function methodA() { $this->methodB(); }
    public function methodB() { $this->x = $this->x + 1; }
    public function methodC() { $this->y = $this->y + 1; }
    public function methodD() { $this->y = $this->y - 1; $this->methodE(); }
    public function methodE() { (new Another)->methodB(); }
    public function getX() { return $this->x; }
}