<?php
namespace {
    class A {
        public function foo($foo, C $c) : H
        {
             $x = new B;
            D::foo();
        }
    }
    class B {
        public function baz(array $array)
        {

        }
    }
    class C {

    }
    class D {

    }
    class E extends D implements F, G {

    }
    interface F extends G, H {

    }
    interface G {

    }
    interface H {

    }
}

namespace NS1 {
    use NS2\B;
    class A {
        public function foo()
        {
            $a = new B;
        }
    }
}
namespace NS2 {
    class B {

    }
}