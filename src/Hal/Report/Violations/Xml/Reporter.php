<?php
/** @noinspection PhpComposerExtensionStubsInspection As ext-dom is not required but suggested. */
declare(strict_types=1);

namespace Hal\Report\Violations\Xml;

use DOMDocument;
use DOMElement;
use DOMException;
use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\File\WriterInterface;
use Hal\Component\Output\Output;
use Hal\Metric\Metrics;
use Hal\Report\ReporterInterface;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use function array_map;
use function date;
use function dirname;
use function sprintf;

/**
 * This class is responsible for the report of violations on XML file.
 */
final class Reporter implements ReporterInterface
{
    private DOMDocument $xml;

    public function __construct(
        private readonly ConfigBagInterface $config,
        private readonly Output $output,
        private readonly WriterInterface $fileWriter,
    ) {
        $this->xml = new DOMDocument('1.0', 'UTF-8');
        $this->xml->formatOutput = true;
    }

    /**
     * {@inheritDoc}
     * @throws DOMException
     */
    public function generate(Metrics $metrics): void
    {
        /** @var null|string $logFile */
        $logFile = $this->config->get('report-violations');
        if (null === $logFile) {
            return;
        }

        $root = $this->xml->createElement('pmd');
        $root->setAttribute('version', '@package_version@');
        $root->setAttribute('timestamp', date('c'));

        foreach ($metrics->all() as $metric) {
            /** @var ViolationsHandlerInterface $violationsHandler */
            $violationsHandler = $metric->get('violations');
            /** @var array<Violation> $violations */
            $violations = $violationsHandler->getAll();
            if ([] === $violations) {
                continue;
            }

            $node = $this->xml->createElement('file');
            /** @var string $name */
            $name = $metric->get('name');
            $node->setAttribute('name', $name);
            array_map(function (Violation $violation) use ($node): void {
                $node->appendChild($this->createXmlViolationItem($violation));
            }, $violations);
            $root->appendChild($node);
        }

        $this->xml->appendChild($root);

        // Save XML file.
        $this->fileWriter->ensureDirectoryExists(dirname($logFile));
        /** @var string $xml */
        $xml = $this->xml->saveXML();
        $this->fileWriter->write($logFile, $xml);

        $this->output->writeln(sprintf('XML report generated in "%s".', $logFile));
    }

    /**
     * Creates an XML element representing the given violation.
     *
     * @param Violation $violation
     * @return DOMElement
     * @throws DOMException
     */
    private function createXmlViolationItem(Violation $violation): DOMElement
    {
        $violationName = $violation->getName();
        $item = $this->xml->createElement('violation');
        $item->setAttribute('beginLine', '1');
        $item->setAttribute('rule', $violationName);
        $item->setAttribute('ruleset', $violationName);
        $item->setAttribute('externalInfoUrl', 'https://www.phpmetrics.org');
        $item->setAttribute('priority', (string)(4 - $violation->getLevel())); // Priority = reversed level.
        $item->nodeValue = $violation->getDescription();

        return $item;
    }
}
