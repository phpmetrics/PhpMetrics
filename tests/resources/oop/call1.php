<?php
class A {
    function foo() {
        (new B)->bar();
        $c = new B;
        $c->baz();
    }
}

(new B($x)->dsfsd())