<?php
/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Ast;

if (PHP_VERSION_ID >= 70000) {
    class_alias(Php7NodeTraverser::class, __NAMESPACE__ . '\\ActualNodeTraverser');
} else {
    class_alias(Php5NodeTraverser::class, __NAMESPACE__ . '\\ActualNodeTraverser');
}

/**
 * Empty class to refer the good ActualNodeTraverser depending on the PHP version.
 * This class must be hard-coded and not directly used as an alias because composer can not handle class-aliases when
 * flag --classmap-authoritative is set.
 * @see https://github.com/phpmetrics/PhpMetrics/issues/373
 */
/** @noinspection PhpUndefinedClassInspection */
class NodeTraverser extends ActualNodeTraverser
{
}
