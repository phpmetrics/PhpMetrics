<?php
declare(strict_types=1);

namespace Hal\Exception\GraphException;

use Hal\Component\Tree\Node;
use Hal\Exception\GraphException;
use function sprintf;

/**
 * Exception thrown when trying to add an edge in a graph but the origin node is not attached to that graph.
 */
final class OriginNodeMissingException extends GraphException
{
    /**
     * @param Node $node
     * @return OriginNodeMissingException
     */
    public static function inGraph(Node $node): OriginNodeMissingException
    {
        return new self(sprintf('The origin node "%s" is not is in the graph', $node->getKey()));
    }
}
