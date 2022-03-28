<?php
declare(strict_types=1);

namespace Hal\Metric\Helper;

use Closure;
use PhpParser\Node;

/**
 * Defines rules about how to iterate over a node using a node traverser. Each method can define its own list of
 * visitors to traverse with the given node and all its sub-nodes.
 */
interface NodeIteratorInterface
{
    /**
     * Iterates over the given node using the given callback as only visitor to use when the node traverser is executed.
     *
     * @param Node $node
     * @param Closure $callback
     * @return void
     */
    public function iterateOver(Node $node, Closure $callback): void;
}
