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
class CacheNull implements Cache {

    /**
     * @inheritdoc
     */
    public function set($key, $value) {
    }

    /**
     * @inheritdoc
     */
    public function get($key) {
    }

    /**
     * @inheritdoc
     */
    public function has($key) {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function clear($key) {
    }
}