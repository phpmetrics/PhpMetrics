<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Reflected;
use Hal\Component\OOP\Resolver\NameResolver;


/**
 * Usage of method
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
interface MethodUsage {

    const USAGE_UNKNWON = 0;
    const USAGE_GETTER = 1;
    const USAGE_SETTER = 2;

};