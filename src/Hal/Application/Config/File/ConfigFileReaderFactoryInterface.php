<?php
declare(strict_types=1);

namespace Hal\Application\Config\File;

/**
 * Interface defining the responsibility of the factory which aims to create an instance of ConfigFileReaderInterface.
 */
interface ConfigFileReaderFactoryInterface
{
    /**
     * Creates an instance of a ConfigFileReaderInterface object based on the extension of the filename
     * given in argument.
     *
     * @param string $filename
     * @return ConfigFileReaderInterface
     */
    public function createFromFileName(string $filename): ConfigFileReaderInterface;
}
