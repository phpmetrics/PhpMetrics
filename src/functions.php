<?php
declare(strict_types=1);

/**
 * @param $src
 * @param $dst
 */
function recurse_copy($src, $dst)
{
    $dir = opendir($src);
    if (!file_exists($dst)) {
        mkdir($dst);
    }
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

/**
 * @return string
 */
function getVersion()
{
    return 'v2.8.2';
}

/**
 * @param array $array
 * @param string $attribute
 * @param mixed $currentValue
 * @return false|float
 */
function gradientAlphaFor($array, $attribute, $currentValue)
{
    // memory cache
    static $caches;
    if(null === $caches) {
        $caches = [];
    }

    if(!isset($caches[$attribute])) {
        // avoid to iterate over array too many times
        $max = 0;
        $min = 1;
        foreach($array as $item) {
            if(!isset($item[$attribute])) {
                continue;
            }

            $max = max($max, $item[$attribute]);
            $min = min($min, $item[$attribute]);
        }

        $caches[$attribute]['max'] = $max;
        $caches[$attribute]['min'] = $min;
    }

    $max = $caches[$attribute]['max'];
    $min = $caches[$attribute]['min'];

    $percent = (($currentValue - $min) * 100) / (max(1, $max - $min));
    return round($percent / 100, 2);
}

/**
 * Style an element according its position in range
 *
 * @param array $array
 * @param string $attribute
 * @param mixed $currentValue
 * @return string
 */
function gradientStyleFor($array, $attribute, $currentValue) {
    return sprintf(' style="background-color: hsla(203, 82%%, 76%%, %s);"', gradientAlphaFor($array, $attribute, $currentValue));
}

/**
 * Calculate percentalies
 *
 * @param float[]|int[] $arr
 * @param float $percentile
 * @return mixed
 */
function percentile($arr, $percentile = 0.95)
{
    sort($arr);
    return $arr[max(round($percentile * count($arr) - 1.0 - $percentile), 0)];
}
