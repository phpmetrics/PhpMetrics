<?php

namespace Hal\Application\Config;

use Hal\Application\Config\File\ConfigFileReaderFactory;

class Parser
{
    public function parse(array $argv)
    {
        $config = new Config();

        if (count($argv) === 0) {
            return $config;
        }

        if (preg_match('!\.php$!', $argv[0]) || preg_match('!phpmetrics$!', $argv[0]) || preg_match('!phpmetrics.phar$!', $argv[0])) {
            array_shift($argv);
        }

        // Checking for a configuration file option key and importing options
        foreach ($argv as $k => $arg) {
            if (preg_match('!\-\-config=(.*)!', $arg, $matches)) {
                $fileReader = ConfigFileReaderFactory::createFromFileName($matches[1]);
                $fileReader->read($config);
                unset($argv[$k]);
            }
        }

        // arguments with options
        foreach ($argv as $k => $arg) {
            if (preg_match('!\-\-([\w\-]+)=(.*)!', $arg, $matches)) {
                list(, $parameter, $value) = $matches;
                $config->set($parameter, trim($value, ' "\''));
                unset($argv[$k]);
            }
        }

        // arguments without options
        foreach ($argv as $k => $arg) {
            if (preg_match('!\-\-([\w\-]+)$!', $arg, $matches)) {
                list(, $parameter) = $matches;
                $config->set($parameter, true);
                unset($argv[$k]);
            }
        }

        // last argument
        $files = array_pop($argv);
        if ($files && !preg_match('!^\-\-!', $files)) {
            $config->set('files', explode(',', $files));
        }

        return $config;
    }
}
