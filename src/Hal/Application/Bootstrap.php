<?php
declare(strict_types=1);

namespace Hal\Application;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Application\Config\ParserInterface;
use Hal\Application\Config\ValidatorInterface;
use Hal\Component\Output\Output;
use Hal\Exception\ConfigException;

/**
 * Bootstrapping class that parses, prepares and validates the user-defined arguments into a usable configuration
 * for the application.
 */
final class Bootstrap
{
    /**
     * @param ParserInterface $parser
     * @param ValidatorInterface $validator
     * @param Output $output
     */
    public function __construct(
        private readonly ParserInterface $parser,
        private readonly ValidatorInterface $validator,
        private readonly Output $output,
    ) {
    }

    /**
     * Prepares the application by validating and normalizing the configuration.
     * After this point, the configuration must not be edited and only read.
     *
     * @param array<int, string> $argv List of raw arguments given by CLI.
     * @return ConfigBagInterface
     */
    public function prepare(array $argv): ConfigBagInterface
    {
        $config = $this->parser->parse($argv);
        try {
            $this->validator->validate($config);
        } catch (ConfigException $e) {
            $config->set('config-error', $e->getMessage());
            return $config;
        }

        if ($config->has('quiet')) {
            $this->output->setQuietMode(true);
        }

        return $config;
    }
}
