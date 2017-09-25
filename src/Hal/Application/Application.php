<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application;

use Hal\Application\Config\Config;
use Hal\Application\Config\ConfigException;
use Hal\Application\Config\Parser;
use Hal\Application\Config\Validator;
use Hal\Component\File\Finder;
use Hal\Component\Output\CliOutput;
use Hal\Component\Issue\Issuer;
use Hal\Report;
use Hal\Violation\ViolationParser;
use InvalidArgumentException;
use Throwable;

/**
 * Class Application
 *
 * Main application entry-point class. Runs the application thanks to a specific config sent through arguments.
 *
 * @package Hal\Application
 */
class Application
{
    /** @var CliOutput CLI output system used for managing STDOUT and STDERR buffers for CLI. */
    private $output;

    /** @var Config Configuration object that contains all configuration parameters for application. */
    private $config;

    /**
     * Application constructor.
     * Set the CLI output and the application configuration.
     *
     * @param array $argv Arguments that must define the configuration of the application.
     * @throws InvalidArgumentException When failed to parse some arguments.
     */
    public function __construct($argv)
    {
        $this->output = new CliOutput();
        $this->parseConfig($argv);

        // Set the QuietMode if defined in the config.
        $this->output->setQuietMode($this->config->has('quiet'));
    }

    /**
     * Parse the arguments in parameters to try to build a Config object.
     *
     * @param array $argv Arguments that must define the configuration of the application.
     * @throws InvalidArgumentException When failed to parse some arguments.
     */
    private function parseConfig($argv)
    {
        $this->config = (new Parser())->parse($argv);
        try {
            (new Validator($this->config))->validate();
        } catch (ConfigException $e) {
            if ($this->config->has('help')) {
                $this->output->writeln(Validator::help());
                exit(0);
            }

            if ($this->config->has('version')) {
                $outputFormat = 'PhpMetrics %s <http://phpmetrics.org>' . \PHP_EOL .
                    'by Jean-François Lépine <https://twitter.com/Halleck45>' . \PHP_EOL;
                $this->output->writeln(\sprintf($outputFormat, \getVersion()));
                exit(0);
            }

            $this->outputException($e)->writeln(Validator::help());
            exit(1);
        }
    }

    /**
     * Runs the application.
     * Execute the main part of the application now the configuration has been defined.
     * Application is doing the following tasks:
     * 1. Fetch all files by conflicting the files and directories given with the extensions and exclusions.
     * 2. Analyse those files and combine those analyse into a metric object.
     * 3. Parse the metric object to detect violations.
     * 4. Report the metric in different file format.
     * 5. Enjoy the end.
     */
    public function run()
    {
        // 1. Fetch all files by conflicting the files and directories given with the extensions and exclusions.
        $finder = new Finder($this->config->get('extensions'), $this->config->get('exclude'));
        $files = $finder->fetch($this->config->get('files'));

        // 2. Analyse those files and combine those analyse into a metric object.
        try {
            $metrics = (new Analyze($this->config, $this->output, new Issuer($this->output)))->run($files);
        } catch (ConfigException $e) {
            $this->outputException($e);
            exit(1);
        }

        // 3. Parse the metric object to detect violations.
        (new ViolationParser($this->config, $this->output))->apply($metrics);

        // 4. Report the metric in different file format.
        (new Report\Cli\Reporter($this->config, $this->output))->generate($metrics);
        (new Report\Html\Reporter($this->config, $this->output))->generate($metrics);
        (new Report\Csv\Reporter($this->config, $this->output))->generate($metrics);
        (new Report\Json\Reporter($this->config, $this->output))->generate($metrics);
        (new Report\Violations\Xml\Reporter($this->config, $this->output))->generate($metrics);

        // 5. Enjoy the end.
        $this->output->writeln(\PHP_EOL . 'Done' . \PHP_EOL);
    }

    /**
     * Standardize the output error when an exception occurs.
     * @param Throwable $e Throwable object we will get its message to output.
     * @return CliOutput
     */
    private function outputException(Throwable $e)
    {
        return $this->output->err(\sprintf(\PHP_EOL .'<error>%s</error>' . \PHP_EOL, $e->getMessage()));
    }
}
