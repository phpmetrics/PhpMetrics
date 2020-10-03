<?php

namespace Hal\Component\Ast;

final class NodeTraverserFactory
{
    /**
     * @param callable|null $stopCondition
     *
     * @return CustomNodeTraverser
     */
    public function getTraverser($stopCondition = null)
    {
        return new Php7NodeTraverser($stopCondition);
    }
}
