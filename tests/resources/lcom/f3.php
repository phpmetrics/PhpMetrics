<?php
class Foo {

    private $x;
    private $y;

    public function methodA() { }
    public function methodB() { $this->x = $this->x + 1; }
    public function methodC() { $this->y = $this->x + 1; }
    public function methodD() { $this->y = $this->y - 1; ; }
    public function methodE() { }
}