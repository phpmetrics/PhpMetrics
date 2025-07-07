<?php
namespace {
    class A {
        public function foo()
        {
             new \NS1\B()->foo();
        }
    }
}

namespace NS1 {
    class B {
        public function foo()
        {

        }
    }
}
