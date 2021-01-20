<?php

namespace Hal\Component\Ast;

use PhpParser\Node;
use PhpParser\NodeTraverser as Mother;

class Php7NodeTraverser extends Mother
{
    /** @var Traverser */
    private $traverser;

    /**
     * @param bool $cloneNodes
     * @param callable|null $stopCondition
     */
    public function __construct($cloneNodes = false, $stopCondition = null)
    {
        parent::__construct();
        $this->traverser = new Traverser($this, $stopCondition);
    }

    public function traverseNode(Node $node): Node
    {
        return parent::traverseNode($node);
    }

    protected function traverseArray(array $nodes): array
    {
        return $this->traverser->traverseArray($nodes, $this->visitors);
    }
}
