<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Config;

/**
 * Class Validator
 *
 * Validator for application configuration.
 * Check all configuration keys/values pairs.
 *
 * @package Hal\Application\Config
 */
class Validator
{
    /** @var Config The application configuration. */
    private $config;

    /**
     * Validator constructor.
     *
     * @param Config $config The application configuration to check.
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Validates all configuration values.
     * @throws ConfigException When the checks are failing.
     */
    public function validate()
    {
        $this->checkFiles()
            ->setDefaults()
            ->checkValueOptions();
    }

    /**
     * Checks the "files" option, which is the list of files to parse with the application.
     * @return $this
     * @throws ConfigException When the files to parse are not defined in the configuration.
     * @throws ConfigException When some files listed in the configuration does not exist.
     */
    private function checkFiles()
    {
        if (!$this->config->has('files')) {
            throw ConfigException::missingFileOption();
        }
        foreach ($this->config->get('files') as $dir) {
            if (!\file_exists($dir)) {
                throw ConfigException::missingFile($dir);
            }
        }

        return $this;
    }

    /**
     * Set the default values for options "extensions" and "exclude".
     * @return $this
     */
    private function setDefaults()
    {
        // Set default value for option "extensions".
        if (!$this->config->has('extensions')) {
            $this->config->set('extensions', 'php,inc');
        }
        $this->config->set('extensions', \explode(',', $this->config->get('extensions')));

        // Set default value for option "exclude".
        if (!$this->config->has('exclude')) {
            $this->config->set(
                'exclude',
                'vendor,test,Test,tests,Tests,testing,Testing,bower_components,node_modules,cache,spec'
            );
        }
        $this->config->set('exclude', \array_filter(\explode(',', $this->config->get('exclude'))));

        return $this;
    }

    /**
     * Checks the options that must have values really have values defined.
     * @throws ConfigException When an option that requires a value does not have a value.
     */
    private function checkValueOptions()
    {
        // Following keys are options that must have a value defined.
        $keys = ['config', 'report-html', 'report-csv', 'report-violation', 'report-json', 'extensions'];

        foreach ($keys as $key) {
            $value = $this->config->get($key);
            if ($this->config->has($key) && (empty($value) || true === $value)) {
                throw ConfigException::missingMandatoryOptionValue($key);
            }
        }
    }

    /**
     * @return string
     */
    public static function help()
    {
        return <<<EOT
Usage:

    phpmetrics [...options...] <directories>

Required:

    <directories>                       List of files/directories to parse, separated by a comma (,)

Optional:

    --config=<file>                     Configuration file in JSON or Ini to set configuration
    --exclude=<directory>               List of directories to exclude, separated by a comma (,)
    --extensions=<php,inc>              List of extensions to parse, separated by a comma (,)
    --git[=</path/to/git_binary>]       Perform analyses based on Git History (default binary path: "git")
    --help                              Display this help output
    --junit[=</path/to/junit.xml>]      Evaluates metrics according to JUnit logs
    --quiet                             Quiet mode
    --report-csv=<file>                 File where report CSV will be generated
    --report-html=<directory>           Folder where report HTML will be generated
    --report-json=<file>                File where report Json will be generated
    --report-violations=<file>          File where XML violations report will be generated
    --version                           Display current version

Examples:

    phpmetrics --report-html="./report" ./src

        Analyze the "./src" directory and generate a HTML report on the "./report" folder


    phpmetrics --report-violations="./build/violations.xml" ./src,./lib

        Analyze the "./src" and "./lib" directories, and generate the "./build/violations.xml" file. This file could 
        be read by any Continuous Integration Platform, and follows the "PMD Violation" standards.

EOT;
    }
}
