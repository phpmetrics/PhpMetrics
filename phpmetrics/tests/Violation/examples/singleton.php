<?php
class MySingleton {
    private function __construct() {}

    public static function foo() {
        return new self;
    }
}