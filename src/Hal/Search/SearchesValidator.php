<?php
declare(strict_types=1);

namespace Hal\Search;

use Hal\Exception\ConfigException\SearchValidationException;
use Hal\Metric\Registry;
use function array_diff;
use function array_intersect_key;
use function array_keys;
use function array_map;
use function in_array;
use function is_array;
use function is_string;

/**
 * This class is responsible for the validation of the configuration options set for the "searches" directive.
 */
final class SearchesValidator implements SearchesValidatorInterface
{
    /**
     * {@inheritDoc}
     */
    public function validates(array $searches): void
    {
        array_map($this->validateSingleSearch(...), $searches);
    }

    /**
     * @param SearchInterface $search
     * @return void
     */
    private function validateSingleSearch(SearchInterface $search): void
    {
        $config = $search->getConfig();

        $allowedKeys = [
            'type',
            'nameMatches',
            'instanceOf',
            'usesClasses',
            'failIfFound',
            ...Registry::allForStructures()
        ];

        if ([] !== array_diff(array_keys($config), $allowedKeys)) {
            throw SearchValidationException::unknownSearchKey($search->getName(), $allowedKeys);
        }

        // Only validates the configuration value of configuration that are defined.
        $configConditions = array_intersect_key(self::getConfigurationValidationConditions(), $config);

        foreach ($configConditions as $configProperty => $validation) {
            if ($validation['condition']($config[$configProperty])) {
                throw $validation['exception'];
            }
        }
    }

    /**
     * Get a list of couple "condition-exception" for each configuration key on which a validation must occur. If the
     * validation fails, then the exception given in the list for the particular element of configuration must be thrown
     * by the validator.
     *
     * @return array<string, array{condition: callable(mixed): bool, exception: SearchValidationException}>
     */
    private static function getConfigurationValidationConditions(): array
    {
        return [
            'type' => [
                'condition' => static fn (mixed $conf): bool => !in_array($conf, ['class', 'interface'], true),
                'exception' => SearchValidationException::invalidType(),
            ],
            'nameMatches' => [
                'condition' => static fn (mixed $conf): bool => !is_string($conf),
                'exception' => SearchValidationException::invalidNameMatches(),
            ],
            'instanceOf' => [
                'condition' => static fn (mixed $conf): bool => !is_array($conf),
                'exception' => SearchValidationException::invalidInstanceOf(),
            ],
            'usesClasses' => [
                'condition' => static fn (mixed $conf): bool => !is_array($conf),
                'exception' => SearchValidationException::invalidUsesClasses(),
            ],
        ];
    }
}
