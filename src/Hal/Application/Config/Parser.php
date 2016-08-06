<?php
namespace Hal\Application\Config;

class Parser
{

    public function parse($argv)
    {
        $config = new Config;

        if (sizeof($argv) === 0) {
            return $config;
        }

        if (preg_match('!\.php$!', $argv[0]) || preg_match('!phpmetrics$!', $argv[0])) {
            array_shift($argv);
        }

        // arguments with options
        foreach ($argv as $k => $arg) {
            if (preg_match('!\-\-([\w\-]+)=(.*)!', $arg, $matches)) {
                list(, $parameter, $value) = $matches;
                $config->set($parameter, $value);
                unset($argv[$k]);
            }
        }

        // last argument
        $files = array_pop($argv);
        if ($files) {
            $config->set('files', explode(',', $files));
        }

        return $config;
    }
}
