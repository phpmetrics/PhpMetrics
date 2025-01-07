<?php
declare(strict_types=1);

namespace Hal\Application\Config;

use Hal\Component\File\SystemInterface;
use Hal\Exception\ConfigException;
use Hal\Metric\Group\Group;
use Hal\Search\SearchesValidatorInterface;
use Hal\Search\SearchInterface;
use function array_filter;
use function array_map;
use function explode;
use function filter_var;
use function implode;
use function is_array;
use function is_string;
use const FILTER_VALIDATE_BOOLEAN;

/**
 * Configuration validator class, that is in charge of validate a given Config instance.
 * Helps the final user to write a valid configuration.
 */
final class Validator implements ValidatorInterface
{
    /**
     * @param SearchesValidatorInterface $searchesValidator
     * @param SystemInterface $fileSystem
     */
    public function __construct(
        private readonly SearchesValidatorInterface $searchesValidator,
        private readonly SystemInterface $fileSystem,
    ) {
    }

    /**
     * @throws ConfigException When the configuration is invalid.
     */
    public function validate(ConfigBagInterface $config): void
    {
        // required
        if (!$config->has('files')) {
            throw ConfigException\NoFileToAnalyseException::configHasNoFilesSet();
        }
        /** @var array<string> $files */
        $files = $config->get('files');
        foreach ($files as $dir) {
            if (!$this->fileSystem->exists($dir)) {
                throw ConfigException\FileDoesNotExistException::fromConfig($dir);
            }
        }

        // extensions
        if (!$config->has('extensions')) {
            $config->set('extensions', 'php,inc');
        }
        /** @var string $extensions */
        $extensions = $config->get('extensions');
        $config->set('extensions', explode(',', $extensions));

        // excluded directories
        if (!$config->has('exclude')) {
            $defaultExclude = 'vendor,test,Test,tests,Tests,testing,Testing,bower_components,node_modules,cache,spec';
            $config->set('exclude', $defaultExclude);
        }

        // retro-compatibility with excludes as string in config files
        /** @var string|array<string> $exclude */
        $exclude = $config->get('exclude');
        if (is_array($exclude)) {
            $config->set('exclude', implode(',', $exclude));
        }
        /** @var string $exclude */
        $exclude = $config->get('exclude');
        $config->set('exclude', array_filter(explode(',', $exclude)));

        // groups by regex
        if (!$config->has('groups')) {
            $config->set('groups', []);
        }
        /** @var array<array{name: string, match: non-empty-string}> $groupsRaw */
        $groupsRaw = $config->get('groups');
        $groups = array_map(static fn (array $raw): Group => new Group($raw['name'], $raw['match']), $groupsRaw);
        $config->set('groups', $groups);

        if (!$config->has('composer')) {
            $config->set('composer', true);
        }
        $config->set('composer', filter_var($config->get('composer'), FILTER_VALIDATE_BOOLEAN));

        // Search
        if (!$config->has('searches')) {
            $config->set('searches', []);
        }
        /** @var array<SearchInterface> $searches */
        $searches = $config->get('searches');
        $this->searchesValidator->validates($searches);

        // parameters with values
        $keys = [
            'report-html' => static fn (mixed $value): bool => is_string($value) && '' !== $value,
            'report-csv' => static fn (mixed $value): bool => is_string($value) && '' !== $value,
            'report-violations' => static fn (mixed $value): bool => is_string($value) && '' !== $value,
            'report-summary-json' => static fn (mixed $value): bool => is_string($value) && '' !== $value,
            'report-openmetrics' => static fn (mixed $value): bool => is_string($value) && '' !== $value,
            'report-json' => static fn (mixed $value): bool => is_string($value) && '' !== $value,
            'config' => static fn (mixed $value): bool => is_string($value) && '' !== $value,
        ];
        foreach ($keys as $key => $validationCallback) {
            if (!$config->has($key)) {
                continue;
            }

            $value = $config->get($key);
            if (!$validationCallback($value)) {
                throw ConfigException\MissingOptionValueException::requireValue($key);
            }
        }
    }
}
