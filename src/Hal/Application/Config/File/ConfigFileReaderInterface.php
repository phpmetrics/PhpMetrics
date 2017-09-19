<?php

namespace Hal\Application\Config\File;

use Hal\Application\Config\Config;

interface ConfigFileReaderInterface
{
    /**
     * @param Config $config
     *
     * @return void
     */
    public function read(Config $config);
}