<?php
declare(strict_types=1);

namespace Hal\Application\Config\File;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Exception\ConfigException\ConfigFileReadingException;
use JsonException;
use function file_get_contents;
use function json_decode;
use const JSON_THROW_ON_ERROR;

/**
 * Reader of a .json configuration file.
 */
final class ConfigFileReaderJson extends AbstractConfigFileReader
{
    /**
     * {@inheritDoc}
     * @throws JsonException when the JSON file is not well JSON encoded.
     */
    public function read(ConfigBagInterface $config): void
    {
        $jsonText = file_get_contents($this->filename);

        if (false === $jsonText) {
            throw ConfigFileReadingException::inJson($this->filename);
        }

        /* @TODO: Remove @noinspection once https://github.com/kalessil/phpinspectionsea/issues/1725 fixed. */
        /** @noinspection JsonEncodingApiUsageInspection */
        $jsonData = json_decode($jsonText, true, flags: JSON_THROW_ON_ERROR);

        $this->normalizeConfig($config, $jsonData);
    }
}
