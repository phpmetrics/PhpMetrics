<?php
function BinSearch ($item, $table, $n)
{
    $bot = 0;
    $top = $n - 1;
    $mid = 0;
    $cmp = 0;
    while ($bot <= $top) {
        $mid = ($bot + $top) / 2;
        if ($table[$mid] == $item)
            return $mid;
        else if (compare($table[$mid], $item) < 0)
            $top = $mid - 1;
        else
            $bot = $mid + 1;
    }
    return -1; // not found
}