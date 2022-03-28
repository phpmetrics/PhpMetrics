<?php
declare(strict_types=1);

namespace Hal\Exception;

use InvalidArgumentException;

/**
 * Groups all exceptions related to a misconfiguration of the application, from user-defined arguments.
 */
abstract class ConfigException extends InvalidArgumentException
{
}
