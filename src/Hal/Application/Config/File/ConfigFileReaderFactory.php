<?php

namespace Hal\Application\Config\File;

class ConfigFileReaderFactory
{
    /**
     * @param string $filename
     *
     * @return ConfigFileReaderInterface
     */
    public static function createFromFileName($filename)
    {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new \InvalidArgumentException("Cannot read configuration file '{$filename}'");
        }

        switch (pathinfo($filename, PATHINFO_EXTENSION)) {
            case 'json':
                return new ConfigFileReaderJson($filename);
            case 'yaml':
            case 'yml':
                return new ConfigFileReaderYaml($filename);
            case 'ini':
                return new ConfigFileReaderIni($filename);
                break;
            default:
                throw new \InvalidArgumentException("Unsupported config file format: '$filename'");
        }
    }
}
