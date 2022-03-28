<?php
declare(strict_types=1);

namespace Hal\Metric\System\Packages\Composer;

use stdClass;

/**
 * This interface provides rules on how to request packages repositories to fetch information on given packages.
 */
interface ComposerRegistryConnectorInterface
{
    /**
     * Get information about the requested package in argument using composer registry connection (Packagist,
     * Repman, ...).
     *
     * @param string $package
     * @return stdClass
     */
    public function get(string $package): stdClass;
}
