<?php
declare(strict_types=1);

namespace Hal\Exception\GraphException;

use Hal\Component\Tree\Node;
use Hal\Exception\GraphException;
use function sprintf;

/**
 * Exception thrown when trying to add an edge in a graph but the target node is not attached to that graph.
 */
final class TargetNodeMissingException extends GraphException
{
    /**
     * @param Node $node
     * @return TargetNodeMissingException
     */
    public static function inGraph(Node $node): TargetNodeMissingException
    {
        return new self(sprintf('The target node "%s" is not is in the graph', $node->getKey()));
    }
}
