<?php
declare(strict_types=1);

namespace Hal\Report\Json;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\Output\Output;
use Hal\Exception\NotWritableJsonReportException;
use Hal\Metric\Metrics;
use Hal\Report\ReporterInterface;
use JsonException;
use function dirname;
use function file_exists;
use function file_put_contents;
use function is_writable;
use function json_encode;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

/**
 * This class is responsible for the report on a JSON file.
 */
final class Reporter implements ReporterInterface
{
    public function __construct(
        private readonly ConfigBagInterface $config,
        private readonly Output $output
    ) {
    }

    /**
     * {@inheritDoc}
     * @throws JsonException
     */
    public function generate(Metrics $metrics): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        $logFile = $this->config->get('report-json');
        if (!$logFile) {
            return;
        }
        if (!file_exists(dirname($logFile)) || !is_writable(dirname($logFile))) {
            throw NotWritableJsonReportException::noPermission($logFile);
        }

        file_put_contents($logFile, json_encode($metrics, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }
}
