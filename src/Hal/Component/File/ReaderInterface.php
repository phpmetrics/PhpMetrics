<?php
declare(strict_types=1);

namespace Hal\Component\File;

use stdClass;

/**
 * Contains proxy methods to PHP functions related to file system readings only.
 */
interface ReaderInterface extends SystemInterface
{
    /**
     * Returns the full content of the given file.
     *
     * @param string $path
     * @return false|string
     */
    public function read(string $path): false|string;

    /**
     * Returns an array of key-value elements inferred from the given ini file. FALSE in case of error.
     *
     * @param string $path
     * @return array<mixed>|false
     */
    public function readIni(string $path): array|false;

    /**
     * Returns the JSON-decoded value of the given file content.
     *
     * @param string $path
     * @return mixed
     */
    public function readJson(string $path): mixed;

    /**
     * Returns the JSON-decoded value of the content fetched via an URI.
     *
     * @param string $uri
     * @return stdClass
     */
    public function httpReadJson(string $uri): stdClass;

    /**
     * Returns the Yaml content of the given file.
     *
     * @param string $path
     * @return mixed
     */
    public function readYaml(string $path): mixed;

    /**
     * Returns true if the given path owns the "read" permission.
     *
     * @param string $path
     * @return bool
     */
    public function isReadable(string $path): bool;
}
