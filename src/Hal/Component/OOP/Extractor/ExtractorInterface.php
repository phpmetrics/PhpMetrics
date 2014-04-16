<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Extractor;
use Hal\Component\Token\TokenCollection;


/**
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
interface ExtractorInterface {


    /**
     * Extract method from position
     *
     * @param $n
     * @param TokenCollection $tokens
     * @return mixed
     */
    public function extract(&$n, TokenCollection $tokens);
};