<?php

namespace Hal\Component\Ast;

use PhpParser\Node;

interface CustomNodeTraverser
{
    /**
     * Recursively traverse a node.
     *
     * @return Node Result of traversal (may be original node or new one)
     */
    public function traverseNode(Node $node);
}
