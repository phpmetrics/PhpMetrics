<?php

namespace Polyfill;

if (!method_exists(\PHPUnit\Framework\Assert::class, 'assertStringContainsString')) {
    trait TestCaseCompatible
    {
        public function assertStringContainsString($needle, $haystack, $message = '')
        {
            $this->assertContains($needle, $haystack, $message);
        }
    }
} else {
    trait TestCaseCompatible
    {
    }
}


