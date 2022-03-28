<?php
declare(strict_types=1);

namespace Hal\Exception\ConfigException;

use Hal\Exception\ConfigException;

/**
 * Exception thrown when the `git` command does not exist.
 * This could be the case if the option `--git` is given in configuration, but the project to analyse is not using Git
 * as versioning repository.
 */
final class GitBinaryIsIncorrectException extends ConfigException
{
    /**
     * @return GitBinaryIsIncorrectException
     */
    public static function invalidCommand(): GitBinaryIsIncorrectException
    {
        return new self('Git binary (git) incorrect');
    }
}
