<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\System\Changes;

use PHPUnit\Framework\TestCase;

final class GitChangesTest extends TestCase
{
    public function testICanHaveGitChangesData(): void
    {
        // TODO: Or do not test: this looks like impossible if still using `shell_exec`.
        self::markTestSkipped(
            'The class to test contains shell_exec usages and I do not know how to test it without side effect.'
        );
    }
}
