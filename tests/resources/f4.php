<?php
function f_while($a){
    $i = 2;
    if ($a < 0) return 0;
    while ($a > 0){
        $a -= 100;
        $i *= 2;
    }
    return $i;
}