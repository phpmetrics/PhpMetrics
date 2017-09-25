<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config;

use Hal\Application\Config\File\ConfigFileReaderFactory;
use InvalidArgumentException;

/**
 * Class Parser
 *
 * Parse the given parameter to build a configuration object that defines the application configuration.
 *
 * @package Hal\Application\Config
 */
class Parser
{
    /** @var Config Configuration application. */
    protected $config;

    /**
     * Constructor.
     * Create a new configuration object that will be populated when parsing the configuration file.
     */
    public function __construct()
    {
        $this->config = new Config();
    }


    /**
     * Build a configuration system for the application based on the arguments given.
     * @param array $argv Arguments that must define the configuration of the application.
     * @return Config
     * @throws InvalidArgumentException When an error occurs when trying to create the config file reader.
     */
    public function parse($argv)
    {
        if (0 === \count($argv)) {
            return $this->config;
        }

        // Remove first argument if ends with ".php", "phpmetrics" or "phpmetrics.phar" as it is the caller.
        if (\preg_match('!(\.php)|(phpmetrics(\.phar)?)$!', $argv[0])) {
            \array_shift($argv);
        }

        // Consume all arguments that need to be set in the configuration application object.
        $argv = \array_filter($argv, [$this, 'consumeArgument']);

        // Manage the last argument: the files list.
        $files = \array_pop($argv);
        if ($files && 0 !== strpos($files, '--')) {
            $this->config->set('files', \explode(',', $files));
        }

        return $this->config;
    }

    /**
     * Consumes the given argument to set it to the configuration application object.
     * When the argument has been consumed, the method returns "false" as the argument must not exist anymore.
     * Returns "true" when the argument still exists because it was not consumed, so not added to the configuration.
     * @param string $argument The given argument we will try to consume.
     * @return bool
     * @throws InvalidArgumentException When failing to read a configuration file given by option --config=<configFile>.
     */
    private function consumeArgument($argument)
    {
        // Checking for a configuration file option key and importing options.
        if (\preg_match('!--config=(.*)!', $argument, $matches)) {
            ConfigFileReaderFactory::createFromFileName($matches[1])->read($this->config);
            return false;
        }

        // Manage arguments with options.
        if (\preg_match('!--([\w\-]+)=(.*)!', $argument, $matches)) {
            list(, $parameter, $value) = $matches;
            $this->config->set($parameter, \trim($value, ' "\''));
            return false;
        }

        // Manage arguments without options.
        if (\preg_match('!--([\w\-]+)$!', $argument, $matches)) {
            list(, $parameter) = $matches;
            $this->config->set($parameter, true);
            return false;
        }

        // Argument is not consumed, so return true.
        return true;
    }
}
