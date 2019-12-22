<?php
namespace Hal\Report\Violations\Xml;

use DOMDocument;
use Hal\Application\Config\Config;
use Hal\Component\Output\Output;
use Hal\Metric\Metrics;
use Hal\Report\ReporterInterface;
use Hal\Violation\Violation;

/**
 * This class takes care about violations to report in XML format.
 */
class Reporter implements ReporterInterface
{
    /** @var Config */
    private $config;

    /** @var Output */
    private $output;

    /**
     * @param Config $config
     * @param Output $output
     */
    public function __construct(Config $config, Output $output)
    {
        $this->config = $config;
        $this->output = $output;
    }

    /**
     * {@inheritDoc}
     */
    public function generate(Metrics $metrics)
    {
        $logFile = $this->config->get('report-violations');
        if (!$logFile) {
            return;
        }

        // map of levels
        $map = [
            Violation::CRITICAL => 1,
            Violation::ERROR => 2,
            Violation::WARNING => 3,
            Violation::INFO => 4,
        ];

        // root
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $root = $xml->createElement('pmd');
        $root->setAttribute('version', '@package_version@');
        $root->setAttribute('timestamp', date('c'));

        foreach ($metrics->all() as $metric) {
            /** @var Violation[] $violations */
            $violations = $metric->get('violations');
            if (0 === count($violations)) {
                continue;
            }

            $node = $xml->createElement('file');
            $node->setAttribute('name', $metric->get('name'));

            foreach ($violations as $violation) {
                $item = $xml->createElement('violation');
                $item->setAttribute('beginline', 1);
                $item->setAttribute('rule', $violation->getName());
                $item->setAttribute('ruleset', $violation->getName());
                $item->setAttribute('externalInfoUrl', 'http://www.phpmetrics.org/documentation/index.html');
                $item->setAttribute('priority', $map[$violation->getLevel()]);
                $item->nodeValue = $violation->getDescription();
                $node->appendChild($item);
            }
            $root->appendChild($node);
        }
        $xml->appendChild($root);

        // save file
        $logDir = dirname($logFile);
        file_exists($logDir) || mkdir($logDir, 0755, true);
        file_put_contents($logFile, $xml->saveXML());

        $this->output->writeln(sprintf('XML report generated in "%s"', $logFile));
    }
}
