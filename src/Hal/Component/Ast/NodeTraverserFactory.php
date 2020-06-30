<?php

namespace Hal\Component\Ast;

final class NodeTraverserFactory
{
    public function getTraverser($cloneNodes = false, $stopCondition = null)
    {
        if (PHP_VERSION_ID >= 70000) {
            return new Php7NodeTraverser($cloneNodes, $stopCondition);
        }

        return new Php5NodeTraverser($cloneNodes, $stopCondition);
    }
}
