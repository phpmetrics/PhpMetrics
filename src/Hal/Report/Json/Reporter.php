<?php
namespace Hal\Report\Json;

use Hal\Application\Config\Config;
use Hal\Metric\Metrics;
use Hal\Report\ReporterInterface;
use RuntimeException;
use function dirname;
use function file_exists;
use function file_put_contents;
use function is_writable;
use function json_encode;
use const JSON_PRETTY_PRINT;

/**
 * This class takes care about the global report in JSON of consolidated metrics.
 */
final class Reporter implements ReporterInterface
{
    /** @var Config */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function generate(Metrics $metrics)
    {
        if ($this->config->has('quiet')) {
            return;
        }

        $logFile = $this->config->get('report-json');
        if (!$logFile) {
            return;
        }

        $logDir = dirname($logFile);
        if (!file_exists($logDir) || !is_writable($logDir)) {
            throw new RuntimeException('You do not have permissions to write JSON report in ' . $logFile);
        }

        file_put_contents($logFile, json_encode($metrics, JSON_PRETTY_PRINT));
    }
}
