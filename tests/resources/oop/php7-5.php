<?php
namespace My;
class MainClass {
    public function foo()
    {
        $c = new class extends Mother {}
    }
}