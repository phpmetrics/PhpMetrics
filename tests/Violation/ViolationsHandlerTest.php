<?php
declare(strict_types=1);

namespace Tests\Hal\Violation;

use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandler;
use Phake;
use PHPUnit\Framework\TestCase;

final class ViolationsHandlerTest extends TestCase
{
    public function testICanAddViolationsAndLoopOnThem(): void
    {
        $violationsHandler = new ViolationsHandler();
        self::assertSame([], $violationsHandler->getAll());
        self::assertCount(0, $violationsHandler);

        $violation = Phake::mock(Violation::class);
        Phake::when($violation)->__call('getName', [])->thenReturn('UnitTestViolation');

        $violationsHandler->add($violation);

        foreach ($violationsHandler as $violationElement) {
            self::assertSame($violationElement, $violation);
        }

        self::assertSame('UnitTestViolation,', (string)$violationsHandler);
    }
}
