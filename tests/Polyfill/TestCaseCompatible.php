<?php

namespace Polyfill;

if (!method_exists(\PHPUnit\Framework\Assert::class, 'assertStringContainsString')) {
    trait TestCaseCompatible
    {
        public function assertStringContainsString($needle, $haystack, $message = '')
        {
            $this->assertStringContainsString($needle, $haystack, $message);
        }

        public function assertMatchesRegularExpression($pattern, $string, $message = '')
        {
            $this->assertMatchesRegularExpression($pattern, $string, $message);
        }
    }
} else {
    trait TestCaseCompatible
    {
    }
}


