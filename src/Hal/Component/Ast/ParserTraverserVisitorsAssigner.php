<?php

namespace Hal\Component\Ast;

use Hal\Metric\Class_\Text\NameVisitor;

/**
 * This class exists for retro compatibility between nikic/php-parser v3, v4 et v5
 */
class ParserTraverserVisitorsAssigner
{
    public function assign(\PhpParser\NodeTraverser $traverser, array $visitors)
    {
        // With nikic/php-parser >= v5, visitors are traversed in LIFO order.
        // With nikic/php-parser < v5, visitors are traversed in FIFO order.

        if (! method_exists('PhpParser\ParserFactory', 'create')) {
            $visitors = array_reverse($visitors);
        }

        foreach ($visitors as $visitor) {
            $traverser->addVisitor($visitor);
        }
    }
}
