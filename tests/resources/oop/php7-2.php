<?php
namespace My;
class Mother {
    public function foo() {
        return 'abc';
    }
}

interface Contract1 {
    public function foo();
}
interface Contract2 {}

$c = new class extends Mother implements Contract1,
    Contract2 {

};

assert('abc' === $c->foo());
