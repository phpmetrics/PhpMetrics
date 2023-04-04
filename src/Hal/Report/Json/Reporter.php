<?php
declare(strict_types=1);

namespace Hal\Report\Json;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\File\WriterInterface;
use Hal\Component\Output\Output;
use Hal\Exception\NotWritableJsonReportException;
use Hal\Metric\Metrics;
use Hal\Report\ReporterInterface;
use function dirname;

/**
 * This class is responsible for the report on a JSON file.
 */
final class Reporter implements ReporterInterface
{
    public function __construct(
        private readonly ConfigBagInterface $config,
        private readonly Output $output,
        private readonly WriterInterface $fileWriter,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function generate(Metrics $metrics): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        /** @var null|string $logFile */
        $logFile = $this->config->get('report-json');
        if (null === $logFile) {
            return;
        }

        if (!$this->fileWriter->exists(dirname($logFile)) || !$this->fileWriter->isWritable(dirname($logFile))) {
            throw NotWritableJsonReportException::noPermission($logFile);
        }

        $this->fileWriter->writePrettyJson($logFile, $metrics);
    }
}
