<?php
declare(strict_types=1);

namespace Hal\Application\Config\File;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\File\ReaderInterface;
use Hal\Metric\Group\Group;
use Hal\Search\Search;
use function array_filter;
use function array_keys;
use function array_map;
use function array_merge;
use function dirname;
use function implode;
use const DIRECTORY_SEPARATOR;

/**
 * Reader of any type of configuration file.
 */
abstract class AbstractConfigFileReader implements ConfigFileReaderInterface
{
    final public function __construct(
        protected readonly string $filename,
        protected readonly ReaderInterface $fileReader,
    ) {
    }

    /**
     * Normalize configuration options set in data and make them apply to the config object given.
     *
     * @param ConfigBagInterface $config The configuration object to fill with the normalized data.
     * @param array<string, mixed> $data The raw options to normalize.
     * @return void
     */
    final protected function normalizeConfig(ConfigBagInterface $config, array $data): void
    {
        $defaultConfiguration = [
            'includes' => [],
            'groups' => [],
            'extensions' => [],
            'composer' => true,
            'searches' => [],
            'excludes' => [],
            'report' => [],
        ];
        /**
         * @var array{
         *     includes: array<string>,
         *     groups: array<Group>,
         *     extensions: array<string>,
         *     composer: bool,
         *     searches: array<string, array<string, mixed>>,
         *     excludes: array<string>,
         *     report: array<string, string>
         * } $parsingConfiguration
         */
        $parsingConfiguration = $data + $defaultConfiguration;
        // Remove all options that are evaluated to an empty array or empty string.
        $options = array_filter([
            'files' => array_map($this->resolvePath(...), $parsingConfiguration['includes']),
            'groups' => $parsingConfiguration['groups'],
            'extensions' => implode(',', $parsingConfiguration['extensions']),
            'composer' => $parsingConfiguration['composer'],
            'searches' => Search::buildListFromArray($parsingConfiguration['searches']),
            'exclude' => implode(',', $parsingConfiguration['excludes']),
            ...array_merge(
                ...array_map(
                    fn (string $type, string $path): array => ['report-' . $type => $this->resolvePath($path)],
                    array_keys($parsingConfiguration['report']),
                    $parsingConfiguration['report']
                )
            )
        ], static fn (mixed $configValue): bool => [] !== $configValue && '' !== $configValue);

        array_map($config->set(...), array_keys($options), $options);
    }

    /**
     * Resolves the given relative path to an absolute path using the configuration file path as base path
     * only if the given path *is* a relative path.
     */
    private function resolvePath(string $path): string
    {
        if (DIRECTORY_SEPARATOR !== $path[0]) {
             return dirname($this->filename) . DIRECTORY_SEPARATOR . $path;
        }
        return $path;
    }
}
