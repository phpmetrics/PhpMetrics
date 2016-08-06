<?php
namespace Hal\Application\Config;

/**
 * Class Validator
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
    }

    /**
     * @return string
     */
    public function help()
    {
        return <<<EOT
Usage:

    phpmetrics [--report-html=<directory>] [--exclude=<directory>] [--quiet] <directory>

Required:

    <directory>                         List of directories to parse, separated by a comma (,)

Optional:

    --exclude=<directory>               List of directories to exclude, separated by a comma (,)
    --report-html=<directory>           Folder where report HTML will be generated
    --quiet                             Quiet mode
    --version                           Display current and version

EOT;
    }
}
