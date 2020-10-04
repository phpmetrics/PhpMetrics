<?php declare(strict_types=1);

use Phpmetrix\CliApplication;
use Phpmetrix\DiFactory;
use Phpmetrix\ExitInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers Phpmetrix\CliApplication
 * @covers Phpmetrix\DiFactory
 *
 * @uses Phpmetrix\Console\CliInput
 * @uses Phpmetrix\Console\Command\AnalyzeCommand
 * @uses Phpmetrix\Runner\Analyzer
 * @uses Phpmetrix\Parser\PhpParser
 * @uses Phpmetrix\Parser\AstTraverser
 */
final class CliApplicationTest extends TestCase
{

    public function testRunning()
    {
        try {
            $exitMock = $this->createMock(ExitInterface::class);
            $app = new CliApplication(DiFactory::container(), $exitMock, '');

            $app->handle(['appname', 'analyse', __DIR__ . '/_empty']);
        } catch (\Throwable $th) {
            $msg = sprintf("Application integration test failed.\nMessage: %s", $th->getMessage());
            $this->fail($msg);
        }

        $this->assertTrue(true); // Success if this assert is executed
    }
}
