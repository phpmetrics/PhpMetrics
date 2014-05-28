<?php
function divide($x, $y)
{
    if ($y != 0)
    {
        return $x / $y;
    }
    else if ($x == 0 && $y > 2)
        /* Condition 1 */
        /* Condition 2 */
        /* Conditional expr 1 */
    {
        return 1;
    }
    else
    {
        die('divide by zero');
        return 0;
    }
}