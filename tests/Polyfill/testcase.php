<?php

/**
 * Polyfill for PHPUnit\Framework\TestCase vs PHPUnit_Framework_TestCase
 */
if (!class_exists('PHPUnit\Framework\TestCase') && class_exists('PHPUnit_Framework_TestCase')) {
    class_alias('PHPUnit_Framework_TestCase', 'PHPUnit\Framework\TestCase');
}
