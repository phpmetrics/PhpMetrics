<?php

namespace Hal\Component\Ast;

/**
 * This class exists for retro compatibility between nikic/php-parser v3, v4 et v5
 */
class ParserFactoryBridge
{
    public function create($kind = null)
    {
        if (!method_exists('PhpParser\ParserFactory', 'createForNewestSupportedVersion')) {
            if(null === $kind) {
                $kind = \PhpParser\ParserFactory::PREFER_PHP7;
            }
            return (new \PhpParser\ParserFactory())->create($kind);
        }

        if ($kind !== null) {
            return (new \PhpParser\ParserFactory())->createForVersion($kind);
        }

        return (new \PhpParser\ParserFactory())->createForNewestSupportedVersion();
    }
}
