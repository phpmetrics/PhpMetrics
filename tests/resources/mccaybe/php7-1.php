<?php
class A {
    public function foo() {

        $a = new class {
            public function bar () {
                if( c1() )
                    f1();
                else
                    f2();

                if( c2() )
                    f3();
                else
                    f4();
            }
        }

        $b = new class {
            public function bar () {
                if( c1() )
                    f1();
                else
                    f2();

                if( c2() )
                    f3();
                else
                    f4();
            }
        }

    }
}