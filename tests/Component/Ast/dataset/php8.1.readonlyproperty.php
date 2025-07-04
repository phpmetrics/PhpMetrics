<?php

class Foo
{
    public readonly int $bar;

    public function __construct(int $bar)
    {
        $this->bar = $bar;
    }
}
