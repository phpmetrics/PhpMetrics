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
        if ($config->get('help')) {
            return new HelpApplication($this->output);
        }
        if ($config->get('metrics')) {
            return new MetricsApplication($this->output);
        }
        if ($config->get('version')) {
            return new VersionApplication($this->output);
        }
        if ($config->get('config-error')) {
            return new ErrorApplication($config, $this->output);
        }

        return null;
    }
}
