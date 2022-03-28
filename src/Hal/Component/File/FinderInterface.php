<?php
declare(strict_types=1);

namespace Hal\Component\File;

/**
 * Provides a way to find files in a list of paths.
 */
interface FinderInterface
{
    /**
     * Find files in list of paths defined in the configuration with extensions and excludes.
     *
     * @param array<string> $pathsList List of paths to analyse against the extensions and exclusions given to the
     *     finder.
     * @return array<string>
     */
    public function fetch(array $pathsList): array;
}
