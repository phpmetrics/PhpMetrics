<?php
declare(strict_types=1);

namespace Hal\Application;

use Hal\Component\Output\Output;
use Hal\Metric\Registry;
use function sprintf;
use function str_pad;
use const PHP_EOL;

/**
 * Application dedicated to only display the list of metrics available in PhpMetrics.
 */
final class MetricsApplication implements ApplicationInterface
{
    public function __construct(private readonly Output $output)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function run(): int
    {
        $help = <<<EOT
Main metrics are:

EOT;

        $definitions = Registry::getDefinitions();
        foreach ($definitions as $metricName => $description) {
            $help .= sprintf("\n    %s%s", str_pad($metricName, 40), $description);
        }

        $this->output->writeln($help . PHP_EOL);
        return 0;
    }
}
