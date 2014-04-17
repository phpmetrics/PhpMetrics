<?php
class Foo {

    private $x;
    private $y;

    public function methodA() { $this->methodB(); }
    public function methodB() { $this->x = $this->x + 1; }
    public function methodC() { $this->y = $this->x + 1; }
    public function methodD() { $this->y = $this->y - 1; $this->methodE(); }
    public function methodE() { }
}