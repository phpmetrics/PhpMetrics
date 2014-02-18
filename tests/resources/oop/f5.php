<?php
namespace My\Example;

use \Example\IAmCalled as Aliased;

class Titi {

    public function foo() {
        $o = new Aliased;
        IAmCalled::bar();
    }

}