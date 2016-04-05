<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Cache;
use Hal\Component\Result\ResultCollection;

/**
 * Cache
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
interface Cache {
    /**
     * @param $key
     * @return mixed
     */
    public function set($key, $value);

    /**
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param $key
     * @return mixed
     */
    public function has($key);

    /**
     * @param $key
     * @return mixed
     */
    public function clear($key);
}
