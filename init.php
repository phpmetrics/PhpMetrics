<?php

spl_autoload_register(function($class) {
    if (false !== strpos($class, 'Hal')) {
        $filename = __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($filename)) {
            require_once(__DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php');
            return true;
        }
    }
}, true, false);

require_once 'vendor/autoload.php';
require_once 'bin/metrics.php';