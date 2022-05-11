<?php
declare(strict_types=1);

namespace Tests\Hal\Application;

use Hal\Application\VersionInfo;
use PHPUnit\Framework\TestCase;

final class VersionInfoTest extends TestCase
{
    public function testICanGetTheVersionOfTheApplication(): void
    {
        self::assertSame('v3.0.0', VersionInfo::getVersion());
    }
}
