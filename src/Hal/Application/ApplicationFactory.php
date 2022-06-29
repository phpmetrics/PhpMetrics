<?php
declare(strict_types=1);

namespace Hal\Application;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\Output\Output;

/**
 * Creates an application that can be run.
 */
final class ApplicationFactory
{
    public function __construct(private readonly Output $output)
    {
    }

    /**
     * Returns an application object that will be executed depending on the content of the configuration.
     * Returns NULL for the default PhpMetrics application, because it requires global data to construct.
     */
    public function buildFromConfig(ConfigBagInterface $config): null|ApplicationInterface
    {
        /** @var bool $help */
        $help = $config->get('help');
        if ($help) {
            return new HelpApplication($this->output);
        }
        /** @var bool $metrics */
        $metrics = $config->get('metrics');
        if ($metrics) {
            return new MetricsApplication($this->output);
        }
        /** @var bool $version */
        $version = $config->get('version');
        if ($version) {
            return new VersionApplication($this->output);
        }
        /** @var null|string $configError */
        $configError = $config->get('config-error');
        if (null !== $configError && '' !== $configError) {
            return new ErrorApplication($config, $this->output);
        }

        return null;
    }
}
