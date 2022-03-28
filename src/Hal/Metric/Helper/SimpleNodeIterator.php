<?php
declare(strict_types=1);

namespace Hal\Metric\Helper;

use Closure;
use PhpParser\Node;
use PhpParser\NodeTraverser;

/**
 * Provides a way to iterate over a given node and all of its sub-nodes using a simple visitor only. A simple visitor is
 * a visitor that only executes the callback given when constructed during the node traversing.
 */
final class SimpleNodeIterator implements NodeIteratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function iterateOver(Node $node, Closure $callback): void
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new SimpleVisitor($callback));
        $traverser->traverse([$node]);
    }
}
