<?php
declare(strict_types=1);

namespace Tests\Hal\DependencyInjection;

use Hal\DependencyInjection\DependencyInjectionProcessor;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\IgnoreClassForCodeCoverage;
use PHPUnit\Framework\TestCase;

/**
 * Ignores DependencyInjectionProcessor::class because this is a technical class that can only be tested functionally.
 */
#[DoesNotPerformAssertions]
#[IgnoreClassForCodeCoverage(DependencyInjectionProcessor::class)]
final class DependencyInjectionProcessorTest extends TestCase
{
    public function test(): void
    {
    }
}
