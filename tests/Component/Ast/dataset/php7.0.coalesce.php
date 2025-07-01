<?php

class Foo
{
    public function bar($a, $b = null)
    {
        return $a ?? $b ?? 3;
    }
}
