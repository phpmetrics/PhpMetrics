<?php

namespace Hal\Application\Config\File;

use Hal\Application\Config\Config;

class ConfigFileReaderIni implements ConfigFileReaderInterface
{
    /**
     * @var string
     */
    private $filename;

    /**
     * ConfigFileReaderIni constructor.
     *
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @param Config $config
     *
     * @return void
     */
    public function read(Config $config)
    {
        $options = parse_ini_file($this->filename);

        if (false === $options) {
            throw new \InvalidArgumentException("Cannot parse configuration file '{$this->filename}'");
        }

        foreach ($options as $name => $value) {
            $config->set($name, $value);
        }
    }
}
