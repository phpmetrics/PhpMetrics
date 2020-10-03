<?php
class MyClassA {

    private $a;

    public function a()
    {
        $this->b();
    }

    public function b()
    {
        $this->x = 1;
    }

    public function c()
    {
        $this->x = 2;
    }


    public function z()
    {
        $this->y();
    }

    public function y()
    {

    }
}