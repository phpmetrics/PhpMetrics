<?php
/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Ast;

if (PHP_VERSION_ID >= 70000) {
    class_alias(Php7NodeTraverser::class, __NAMESPACE__ . '\\NodeTraverser');
} else {
    class_alias(Php5NodeTraverser::class, __NAMESPACE__ . '\\NodeTraverser');
}
