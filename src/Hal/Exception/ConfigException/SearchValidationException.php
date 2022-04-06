<?php
declare(strict_types=1);

namespace Hal\Exception\ConfigException;

use Hal\Exception\ConfigException;
use function implode;
use function sprintf;

/**
 * Exception thrown when the configuration of the "searches" directive is invalid.
 */
final class SearchValidationException extends ConfigException
{
    /**
     * @param string $unknownKey
     * @param array<string> $allowedKeys
     * @return SearchValidationException
     */
    public static function unknownSearchKey(string $unknownKey, array $allowedKeys): SearchValidationException
    {
        $msg = sprintf('Invalid config for search "%s". Allowed keys: {%s}', $unknownKey, implode(', ', $allowedKeys));
        return new self($msg);
    }

    /**
     * @return SearchValidationException
     */
    public static function invalidType(): SearchValidationException
    {
        return new self('Invalid config for "type". Should be "class" or "interface"');
    }

    /**
     * @return SearchValidationException
     */
    public static function invalidNameMatches(): SearchValidationException
    {
        return new self('Invalid config for "nameMatches". Should be a regex');
    }

    /**
     * @return SearchValidationException
     */
    public static function invalidInstanceOf(): SearchValidationException
    {
        return new self('Invalid config for "instanceOf". Should be an array of classnames');
    }

    /**
     * @return SearchValidationException
     */
    public static function invalidUsesClasses(): SearchValidationException
    {
        $msg = 'Invalid config for "usesClasses". Should be an array of classnames or regexes matching classnames';
        return new self($msg);
    }

    /**
     * @param string $configMetricValue
     * @return SearchValidationException
     */
    public static function invalidCustomMetricComparison(string $configMetricValue): SearchValidationException
    {
        return new self('Invalid search expression for key ' . $configMetricValue);
    }
}
