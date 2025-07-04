<?php

namespace Hal\Application\Config;

use Hal\Metric\Definitions;
use Hal\Metric\Group\Group;
use Hal\Metric\Registry;
use Hal\Search\Searches;
use Hal\Search\SearchesValidator;

/**
 * @package Hal\Application\Config
 */
class Validator
{
    /**
     * @param Config $config
     * @throws ConfigException
     */
    public function validate(Config $config)
    {
        // required
        if (!$config->has('files')) {
            throw new ConfigException('Directory to parse is missing or incorrect');
        }
        foreach ($config->get('files') as $dir) {
            if (!file_exists($dir)) {
                throw new ConfigException(sprintf('Directory %s does not exist', $dir));
            }
        }

        // extensions
        if (!$config->has('extensions')) {
            $config->set('extensions', 'php,inc');
        }
        $config->set('extensions', explode(',', $config->get('extensions')));

        // excluded directories
        if (!$config->has('exclude')) {
            $config->set(
                'exclude',
                'vendor,test,Test,tests,Tests,testing,Testing,bower_components,node_modules,cache,spec'
            );
        }

        // retro-compatibility with excludes as string in config files
        if (is_array($config->get('exclude'))) {
            $config->set('exclude', implode(',', $config->get('exclude')));
        }
        $config->set('exclude', array_filter(explode(',', $config->get('exclude'))));

        // groups by regex
        if (!$config->has('groups')) {
            $config->set('groups', []);
        }
        $groupsRaw = $config->get('groups');

        $groups = array_map(static function (array $groupRaw) {
            return new Group($groupRaw['name'], $groupRaw['match']);
        }, $groupsRaw);
        $config->set('groups', $groups);

        if (!$config->has('composer')) {
            $config->set('composer', true);
        }

        if (function_exists('filter_var')) {
            $config->set('composer', filter_var($config->get('composer'), FILTER_VALIDATE_BOOLEAN));
        } else {
            // When PHP is not compiled with the filter extension, we need to do it manually
            $bool = $config->get('composer');
            if( is_string($bool) ) {
                $bool = strtolower($bool);
                $bool = in_array($bool, ['true', '1', 'yes', 'on'], true);
            }
            $config->set('composer', (bool) $bool);
        }

        // Search
        $validator = new SearchesValidator();
        if (null === $config->get('searches')) {
            $config->set('searches', new Searches());
        }
        $validator->validates($config->get('searches'));

        // parameters with values
        $keys = ['report-html', 'report-csv', 'report-violation', 'report-json', 'report-summary-json', 'extensions', 'config'];
        foreach ($keys as $key) {
            $value = $config->get($key);
            if ($config->has($key) && empty($value) || true === $value) {
                throw new ConfigException(sprintf('%s option requires a value', $key));
            }
        }
    }

    /**
     * @return string
     */
    public function help()
    {
        return <<<EOT
Usage:

    phpmetrics [...options...] <directories>

Required:

    <directories>                     List of directories to parse, separated by a comma (,)

Optional:

    --config=<file>                   Use a file for configuration. File can be a JSON, YAML or INI file.
    --exclude=<directory>             List of directories to exclude, separated by a comma (,)
    --extensions=<php,inc>            List of extensions to parse, separated by a comma (,)
    --metrics                         Display list of available metrics
    --report-html=<directory>         Folder where report HTML will be generated
    --report-csv=<file>               File where report CSV will be generated
    --report-json=<file>              File where report Json will be generated
    --report-summary-json=<file>      File where the summary report Json will be generated
    --report-violations=<file>        File where XML violations report will be generated
    --git[=</path/to/git_binary>]     Perform analyses based on Git History (default binary path: "git")
    --junit[=</path/to/junit.xml>]    Evaluates metrics according to JUnit logs
    --quiet                           Quiet mode
    --version                         Display current version

Examples:

    phpmetrics --report-html="./report" ./src

        Analyze the "./src" directory and generate a HTML report on the "./report" folder


    phpmetrics --report-violations="./build/violations.xml" ./src,./lib

        Analyze the "./src" and "./lib" directories, and generate the "./build/violations.xml" file. This file could
        be read by any Continuous Integration Platform, and follows the "PMD Violation" standards.
EOT;
    }

    public function metrics()
    {
        $help = <<<EOT
Main metrics are:
EOT;

        $registry = new Registry();
        $definitions = $registry->getDefinitions();
        foreach ($definitions as $key => $description) {
            $help .= sprintf("\n    %s%s", str_pad($key, 40, ' ', STR_PAD_RIGHT), $description);
        }

        $help .= PHP_EOL;
        return $help;
    }
}
