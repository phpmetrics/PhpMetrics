<?php
namespace My;
class Mother {
    public function foo() {
        return 'abc';
    }
}

$c = new class extends Mother {

};
assert('abc' === $c->foo());
