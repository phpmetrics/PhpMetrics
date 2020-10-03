<?php
function f_switch($a){
    switch ($a){
        case 0 :
            printf("zero");
        case 10 :
            printf("an even number");
            break;
        default:
            printf("I don't know that one");
            return -1;
            break;
    }
    return 0;
}