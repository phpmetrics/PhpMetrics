<?php
declare(strict_types=1);

namespace Hal\Application\Config;

use Hal\Exception\ConfigException;
use Hal\Metric\Group\Group;
use Hal\Search\SearchesValidatorInterface;
use function array_filter;
use function array_map;
use function explode;
use function file_exists;
use function filter_var;
use function implode;
use function is_array;
use function is_string;
use function shell_exec;
use const FILTER_VALIDATE_BOOLEAN;

/**
 * Configuration validator class, that is in charge of validate a given Config instance.
 * Helps the final user to write a valid configuration.
 */
final class Validator implements ValidatorInterface
{
    /**
     * @param SearchesValidatorInterface $searchesValidator
     */
    public function __construct(private readonly SearchesValidatorInterface $searchesValidator)
    {
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
        foreach ($config->get('files') as $dir) {
            if (!file_exists($dir)) {
                throw ConfigException\FileDoesNotExistException::fromConfig($dir);
            }
        }

        // extensions
        if (!$config->has('extensions')) {
            $config->set('extensions', 'php,inc');
        }
        $config->set('extensions', explode(',', $config->get('extensions')));

        // excluded directories
        if (!$config->has('exclude')) {
            $defaultExclude = 'vendor,test,Test,tests,Tests,testing,Testing,bower_components,node_modules,cache,spec';
            $config->set('exclude', $defaultExclude);
        }

        // retro-compatibility with excludes as string in config files
        if (is_array($config->get('exclude'))) {
            $config->set('exclude', implode(',', $config->get('exclude')));
        }
        $config->set('exclude', array_filter(explode(',', $config->get('exclude'))));

        // groups by regex
        if (!$config->has('groups')) {
            $config->set('groups', []);
        }
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
        $this->searchesValidator->validates($config->get('searches'));

        // parameters with values
        $keys = [
            'report-html' => static fn (mixed $value): bool => is_string($value) && '' !== $value,
            'report-csv' => static fn (mixed $value): bool => is_string($value) && '' !== $value,
            'report-violation' => static fn (mixed $value): bool => is_string($value) && '' !== $value,
            'report-summary-json' => static fn (mixed $value): bool => is_string($value) && '' !== $value,
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
