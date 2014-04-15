<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Result;


/**
 * "as array" interface
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
interface ExportableInterface {

    /**
     * Represents result as array
     *
     * @return array
     */
    public function asArray();
}