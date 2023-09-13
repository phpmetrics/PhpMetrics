<?php
class A {

    public function foo($a, $b, $c)
    {
        if(true) {
            return $c;
        } else {
            return $b;
        }

        self::bar();
        static::bar();
        $this->foo();

        (new B)->bar();
        $B = new B;
        $B->bar();
        (new C)->bar();

        $B::foo();
        B::foo();
        C::foo();
    }

    public static function bar()
    {

    }
}

class B {

    public function bar()
    {

    }

    public static function foo()
    {

    }
}

class C {

    public function bar()
    {

    }

    public static function foo()
    {

    }
}
