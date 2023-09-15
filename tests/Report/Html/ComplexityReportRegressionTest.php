<?php
/** @noinspection PhpComposerExtensionStubsInspection As ext-dom is not required but suggested. */
declare(strict_types=1);

namespace Tests\Hal\Report\Html;

use DOMNode;
use Hal\Application\Config\ConfigBagInterface;
use Hal\Application\VersionInfo;
use Hal\Component\File\ReaderInterface;
use Hal\Component\File\WriterInterface;
use Hal\Component\Output\Output;
use Hal\Metric\Group\Group;
use Hal\Metric\Metrics;
use Hal\Report\Html\Reporter;
use Hal\Report\Html\ViewHelper;
use JsonException;
use Phake;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;
use function array_map;
use function iterator_to_array;
use function sprintf;
use function uniqid;

final class ComplexityReportRegressionTest extends TestCase
{
    /**
     * @return void
     * @throws JsonException
     */
    public function testComplexityHtmlReportContainsCorrectOrderOfTableColumns(): void
    {
        $config = Phake::mock(ConfigBagInterface::class);
        $output = Phake::mock(Output::class);
        $fileWriter = Phake::mock(WriterInterface::class);
        $fileReader = Phake::mock(ReaderInterface::class);
        $fakeSemver = <<<'TXT'
        :major: 1
        :minor: 2
        :patch: 3
        :special: ''
        TXT;
        Phake::when($fileReader)->__call('read', ['.semver'])->thenReturn($fakeSemver);
        (new VersionInfo($fileReader))->inferVersionFromSemver('.semver');

        Phake::when($fileWriter)->__call('write', [Phake::anyParameters()])->thenDoNothing();
        Phake::when($fileWriter)->__call('isWritable', [Phake::anyParameters()])->thenReturn(true);
        Phake::when($fileWriter)->__call('exists', [Phake::anyParameters()])->thenReturn(true);
        Phake::when($fileWriter)->__call('ensureDirectoryExists', [Phake::anyParameters()])->thenDoNothing();
        Phake::when($fileReader)->__call('isReadable', [Phake::anyParameters()])->thenReturn(true);
        Phake::when($fileReader)->__call('exists', [Phake::anyParameters()])->thenReturn(true);

        $reporter = new Reporter($config, $output, $fileWriter, $fileReader, new ViewHelper());

        // prepares data for report
        $groups = [new Group('group', '.*')];
        Phake::when($config)->__call('get', ['groups'])->thenReturn($groups);

        // prepares destination
        $destination = '/tmp/phpmetrics-html' . uniqid('', true);
        Phake::when($config)->__call('get', ['report-html'])->thenReturn($destination);

        // generates report
        $reporter->generate(new Metrics());

        // ensure complexity report contains expected table header columns
        $complexityFile = sprintf('%s/complexity.html', $destination);
        $verifyWrite = Phake::verify($fileWriter)->__call('write', [$complexityFile, Phake::ignoreRemaining()]);
        $actualContent = $verifyWrite[0]->getCall()->getArguments()[1];
        $actualTableHeader = $this->getActualTableHeader($actualContent);
        $expectedTableHeader = [
            'Class',
            'WMC',
            'CC',
            'Max MC',
            'System comp.',
            'Data comp.',
            'Structural comp.',
            'Bugs',
            'Defects',
        ];

        self::assertSame($expectedTableHeader, $actualTableHeader);
    }

    /**
     * @param string $content
     * @return array<string>
     */
    private function getActualTableHeader(string $content): array
    {
        $headerColumns = (new Crawler($content))
            ->filterXPath('.//table[contains(concat(" ",normalize-space(@class)," ")," js-sort-table ")]/thead/tr')
            ->children();

        return array_map(static fn (DOMNode $node): string => $node->textContent, iterator_to_array($headerColumns));
    }
}
