<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\OOP\Extractor;


/**
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
interface ExtractorInterface {


    /**
     * Extract method from position
     *
     * @param $n
     * @param $tokens
     * @return mixed
     */
    public function extract(&$n, $tokens);
};