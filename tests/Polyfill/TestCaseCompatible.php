<?php

namespace Polyfill;

if (!method_exists(\PHPUnit\Framework\Assert::class, 'assertStringContainsString')) {
    trait TestCaseCompatible
    {
        public function assertStringContainsString($needle, $haystack, $message = '')
        {
            $this->assertContains($needle, $haystack, $message);
        }

        public function assertMatchesRegularExpression($pattern, $string, $message = '')
        {
            $this->assertRegExp($pattern, $string, $message);
        }
    }
} else {
    trait TestCaseCompatible
    {
    }
}


