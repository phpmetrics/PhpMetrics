<?php
/** @noinspection PhpComposerExtensionStubsInspection ext-yaml is suggested, but not mandatory. */
declare(strict_types=1);

namespace Hal\Component\File;

use JsonException;
use stdClass;
use function file_get_contents;
use function is_readable;
use function json_decode;
use function parse_ini_file;
use function str_replace;
use function stream_context_create;
use function yaml_parse_file;
use const JSON_THROW_ON_ERROR;

/**
 * Contains proxy methods to PHP functions related to file system readings only.
 */
final class Reader extends System implements ReaderInterface
{
    /**
     * {@inheritDoc}
     */
    public function read(string $path): false|string
    {
        return file_get_contents($path);
    }

    /**
     * {@inheritDoc}
     */
    public function readIni(string $path): array|false
    {
        return parse_ini_file($path, process_sections: true);
    }

    /**
     * {@inheritDoc}
     * @throws JsonException
     */
    public function readJson(string $path): mixed
    {
        if (false === $this->isReadable($path)) {
            return false;
        }
        /** @var string $content as file is readable. */
        $content = $this->read($path);
        /* @TODO: Remove @noinspection once https://github.com/kalessil/phpinspectionsea/issues/1725 fixed. */
        /** @noinspection JsonEncodingApiUsageInspection */
        return json_decode($content, true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * {@inheritDoc}
     * @throws JsonException
     * @infection-ignore-all Cannot be tested as file_get_contents is calling a URI, and we cannot fake its results.
     * @codeCoverageIgnore Cannot be tested as file_get_contents is calling a URI, and we cannot fake its results.
     */
    public function httpReadJson(string $uri): stdClass
    {
        $_SERVER += ['HTTP_PROXY' => ''];
        $httpOptions = ['ignore_errors' => true];
        if ('' !== $_SERVER['HTTP_PROXY']) {
            $httpOptions += [
                'proxy' => str_replace(['http://', 'https://'], 'tcp://', $_SERVER['HTTP_PROXY']),
                'request_fulluri' => true,
            ];
        }
        $content = file_get_contents($uri, context: stream_context_create(['http' => $httpOptions]));
        if (false === $content) {
            return (object)[];
        }

        /* @TODO: Remove @noinspection once https://github.com/kalessil/phpinspectionsea/issues/1725 fixed. */
        /** @noinspection JsonEncodingApiUsageInspection */
        /** @var stdClass */
        return json_decode($content, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * {@inheritDoc}
     */
    public function readYaml(string $path): mixed
    {
        return yaml_parse_file($path) ?? [];
    }

    /**
     * {@inheritDoc}
     */
    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }
}
