<?php


namespace Hal\Component\Ast;

use PhpParser\Node;
use PhpParser\Node\Stmt;

class NodeTyper {
    public static function isOrganizedStructure($node)
    {
        if ($node instanceof Stmt\Class_
            || $node instanceof Stmt\Interface_
            || $node instanceof Stmt\Trait_
            // || $node instanceof Node\Identifier // non namespaced name
        ) {
            return true;
        }

        return false;
    }

    public static function isOrganizedLogicalClassStructure($node)
    {
        if ($node instanceof Stmt\Class_
            || $node instanceof Stmt\Trait_
          //  || $node instanceof Node\Identifier // non namespaced name
        ) {
            return true;
        }

        return false;
    }

}
