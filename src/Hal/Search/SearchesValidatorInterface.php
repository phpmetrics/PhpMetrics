<?php
declare(strict_types=1);

namespace Hal\Search;

/**
 * Provide a method to validate the configuration associated to the researches of violations.
 */
interface SearchesValidatorInterface
{
    /**
     * Validates the configuration set for the "searches" directive.
     * Should throw an exception when the configuration is invalid.
     *
     * @param array<SearchInterface> $searches
     * @return void
     */
    public function validates(array $searches): void;
}
