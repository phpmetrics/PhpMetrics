<?php

class Foo
{
    public function bar($a)
    {
        return $a |> strtolower(...) |> trim(...);
    }
}
