<?php
declare(strict_types=1);

namespace Hal\Application\Config;

/**
 * Defines rules to parse configurations
 */
interface ParserInterface
{
    /**
     * Parses the user-defined arguments into a configuration for the application.
     *
     * @param array<int, string> $argv List of raw arguments given by CLI.
     * @return ConfigBagInterface A configuration object (not-yet validated) built from the arguments.
     */
    public function parse(array $argv): ConfigBagInterface;
}
