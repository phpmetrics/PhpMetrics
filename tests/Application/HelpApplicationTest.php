<?php
declare(strict_types=1);

namespace Tests\Hal\Application;

use Hal\Application\HelpApplication;
use Hal\Component\Output\Output;
use Phake;
use PHPUnit\Framework\TestCase;

final class HelpApplicationTest extends TestCase
{
    /**
     * Simply check the "help" disclaimer isn't changing without updating the unit tests.
     */
    public function testICanRunHelpApplication(): void
    {
        $mockOutput = Phake::mock(Output::class);

        $app = new HelpApplication($mockOutput);
        Phake::when($mockOutput)->__call('writeln', [Phake::anyParameters()])->thenDoNothing();

        $exitStatus = $app->run();

        self::assertSame(0, $exitStatus);
        Phake::verify($mockOutput)->__call('writeln', [self::getExpectedHelpContent()]);
        Phake::verifyNoOtherInteractions($mockOutput);
    }

    /**
     * @return string
     */
    private static function getExpectedHelpContent(): string
    {
        return <<<'EOT'
Usage:

    phpmetrics [...options...] <directories>

Required:

    <directories>                     List of directories to parse, separated by a comma (,)

Optional:

    --config=<file>                   Use a file for configuration. File can be a JSON, YAML or INI file.
    --exclude=<directory>             List of directories to exclude, separated by a comma (,)
    --extensions=<php,inc>            List of extensions to parse, separated by a comma (,)
    --metrics                         Display list of available metrics
    --report-html=<directory>         Folder where report HTML will be generated
    --report-csv=<file>               File where report CSV will be generated
    --report-json=<file>              File where report Json will be generated
    --report-summary-json=<file>      File where the summary report Json will be generated
    --report-violations=<file>        File where XML violations report will be generated
    --junit[=</path/to/junit.xml>]    Evaluates metrics according to JUnit logs
    --quiet                           Enable the quiet mode
    --version                         Display current version

Examples:

    phpmetrics --report-html="./report" ./src

        Analyse the "./src" directory and generate an HTML report on the "./report" folder

    phpmetrics --report-violations="./build/violations.xml" ./src,./lib

        Analyse the "./src" and "./lib" directories, and generate the "./build/violations.xml" file. This file could
        be read by any Continuous Integration Platform, and follows the "PMD Violation" standards.

EOT;
    }
}
