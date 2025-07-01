<?php

//
// This code is a copy of https://github.com/ChristiaanBye/polyfill-each
// @see https://github.com/ChristiaanBye/polyfill-each/blob/master/LICENSE
//
// @licence MIT
// @author ChristiaanBye
//
if (!function_exists('each')) {
    /**
     * @param array $array Although the actual function accepted objects, it was discouraged to pass them. Hence for the
     *                     shim only an array is supported.
     *
     * @return array|false
     */
    function each(array &$array)
    {
        $key   = key($array);
        $value = current($array);

        if ($key === null) {
            // key() returns null if the array pointer is beyond the list of element or if the array is empty. If the
            // same scenario occurred in the each() function, a false was returned instead. Hence returning false here
            return false;
        }

        // Advance the array pointer before returning
        next($array);

        return array(
            1       => $value,
            'value' => $value,
            0       => $key,
            'key'   => $key
        );
    }
}
