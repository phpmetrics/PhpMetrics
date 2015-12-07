<?php
namespace My\Example;

use \Full\AliasedClass as Another;

abstract class Titi {

    public abstract function bar(Another &$t1, Toto $t2);

    public function baz() {
        $c = new \StdClass;
    }

}
