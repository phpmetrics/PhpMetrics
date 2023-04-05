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

        $violations = [
            Phake::mock(Violation::class),
            Phake::mock(Violation::class),
        ];
        Phake::when($violations[0])->__call('getName', [])->thenReturn('UnitTestViolation');
        Phake::when($violations[1])->__call('getName', [])->thenReturn('AnotherViolation');

        foreach ($violations as $violation) {
            $violationsHandler->add($violation);
        }

        self::assertSame('UnitTestViolation,AnotherViolation,', (string)$violationsHandler);

        foreach ($violationsHandler as $index => $violationElement) {
            self::assertSame($violationElement, $violations[$index]);
            Phake::verify($violation)->__call('getName', []);
            Phake::verifyNoOtherInteractions($violation);
        }
    }
}
