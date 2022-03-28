<?php
declare(strict_types=1);

namespace Hal\Exception\GraphException;

use Hal\Component\Tree\Node;
use Hal\Exception\GraphException;
use function sprintf;

/**
 * Exception thrown when a node is already defined in a graph.
 */
final class NodeAlreadyDefinedException extends GraphException
{
    /**
     * @param Node $node
     * @return NodeAlreadyDefinedException
     */
    public static function inGraph(Node $node): NodeAlreadyDefinedException
    {
        return new self(sprintf('Node %s is already defined', $node->getKey()));
    }
}
