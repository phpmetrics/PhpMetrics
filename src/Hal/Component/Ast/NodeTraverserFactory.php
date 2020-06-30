<?php

namespace Hal\Component\Ast;

final class NodeTraverserFactory
{
    /** @return CustomNodeTraverser */
    public function getTraverser($stopCondition = null)
    {
        if (PHP_VERSION_ID >= 70000) {
            return new Php7NodeTraverser($stopCondition);
        }

        return new Php5NodeTraverser($stopCondition);
    }
}
