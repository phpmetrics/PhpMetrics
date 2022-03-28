<?php
declare(strict_types=1);

namespace Hal\Application;

/**
 * Represents an entrypoint of any application.
 */
interface ApplicationInterface
{
    /**
     * Entrypoint of the application.
     *
     * @return int The exit status code of the application process. Should be [0-255].
     */
    public function run(): int;
}
