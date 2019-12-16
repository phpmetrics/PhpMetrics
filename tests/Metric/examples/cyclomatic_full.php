<?php
class SwitchCase // ccn2: 4
{
    function __invoke()
    {
        switch ('abc') {
            case 'abc':
            case 'def':
            case 'hij':
                break;
            default:
        }
    }
}

class IfElseif // ccn2: 7
{
    function __invoke()
    {
        if (true) {
            if (true) {
            } elseif (true) {
            } else {
            }
        } elseif (true) {
            if (false) {
            }
        }

        if (true) {
        }
    }
}

class Loops // ccn2: 5
{
    function __invoke()
    {
        while (true) {
            do {
            } while (false);
        }
        foreach ([] as $each) {
            for ($i = 0; $i < 0; ++$i) {
            }
        }
    }
}

class CatchIt // ccn2: 3
{
    function __invoke()
    {
        try {
        } catch (Exception $e) {
        } catch (Throwable $e) {
        } finally {
        }
    }
}

class Logical // ccn2: 11
{
    function __invoke()
    {
        $a = (true || false) && (false && true) || (true xor false);
        $b = $a ? 1 : 2;
        $c = $b ?: 0;
        $d = $b ?? $c;
        $e = $b <=> $d;
    }
}
