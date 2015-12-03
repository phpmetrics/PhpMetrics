<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Reflected\ReflectedClass;
use Hal\Component\OOP\Reflected\ReflectedClass;


/**
 * Result (class)
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ReflectedAnonymousClass extends ReflectedClass {

    /**
     * @inheritdoc
     */
    public function getNamespace() {
        return '\\';
    }
};