<?php
declare(strict_types=1);

namespace Hal\Exception\GraphException;

use Hal\Exception\GraphException;

/**
 * Exception thrown when trying to calculate the height of a cyclic graph, which is impossible.
 */
final class NoSizeForCyclicGraphException extends GraphException
{
    /**
     * @return NoSizeForCyclicGraphException
     */
    public static function incalculableSize(): NoSizeForCyclicGraphException
    {
        return new self('Cannot get size information of cyclic graph');
    }
}
