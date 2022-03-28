<?php
declare(strict_types=1);

namespace Hal\Application\Config;

/**
 * Provides rules to help in validate the configuration set for the current run of PhpMetrics.
 */
interface ValidatorInterface
{
    /**
     * Validates the given configuration.
     *
     * @param ConfigBagInterface $config
     * @return void
     */
    public function validate(ConfigBagInterface $config): void;
}
