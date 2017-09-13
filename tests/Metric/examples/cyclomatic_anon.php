<?php

declare(strict_types=1);

namespace Foo;

class C
{
    public function foo1()
    {
        return new class
        {
        };
    }
}
