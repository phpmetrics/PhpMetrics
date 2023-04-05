<?php
declare(strict_types=1);

namespace Hal\Application\Config\File;

use Hal\Component\File\ReaderInterface;
use InvalidArgumentException;
use function pathinfo;
use function sprintf;
use const PATHINFO_EXTENSION;

/**
 * Factory class to create an instance of ConfigFileReaderInterface.
 */
final class ConfigFileReaderFactory implements ConfigFileReaderFactoryInterface
{
    public function __construct(private readonly ReaderInterface $fileReader)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function createFromFileName(string $filename): ConfigFileReaderInterface
    {
        if (!$this->fileReader->exists($filename) || !$this->fileReader->isReadable($filename)) {
            throw new InvalidArgumentException(sprintf('Cannot read configuration file "%s".', $filename));
        }

        return match (pathinfo($filename, PATHINFO_EXTENSION)) {
            'json' => new ConfigFileReaderJson($filename, $this->fileReader),
            'yaml', 'yml' => new ConfigFileReaderYaml($filename, $this->fileReader),
            'ini' => new ConfigFileReaderIni($filename, $this->fileReader),
            default => throw new InvalidArgumentException(sprintf('Unsupported config file format: "%s".', $filename)),
        };
    }
}
