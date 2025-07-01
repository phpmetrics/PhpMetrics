<?php

require_once __DIR__ . '/../vendor/autoload.php';

// polyfill for methods of phpunit
require_once __DIR__ . '/Polyfill/TestCaseCompatible.php';

// polyfill for each() function
require_once __DIR__ . '/Polyfill/each.php';

// polyfill for \PHPUnit\Framework\TestCase vs \PHPUnit_Framework_TestCase
require_once __DIR__ . '/Polyfill/testcase.php';
