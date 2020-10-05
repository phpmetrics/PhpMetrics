<?php declare(strict_types=1);

use Phpmetrix\CliApplication;
use Phpmetrix\DiFactory;
use Phpmetrix\ExitInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Phpmetrix\CliApplication
 * @covers \Phpmetrix\DiFactory
 * @covers \Phpmetrix\Console\Command\AnalyzeCommand
 */
final class CliApplicationTest extends TestCase
{

    public function testRunning()
    {
        try {
            $exitMock = $this->createMock(ExitInterface::class);
            $rules[ExitInterface::class] = ['instanceOf' => $exitMock];

            $app = DiFactory::container($rules)->get(CliApplication::class);
            $app->handle(['appname', 'analyse', __DIR__ . '/_empty']);
        } catch (\Throwable $th) {
            $msg = sprintf("Application integration test failed.\nMessage: %s", $th->getMessage());
            $this->fail($msg);
        }

        $this->assertTrue(true); // Success if this assert is executed
    }
}
