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
class CacheMemory implements Cache {

    /**
     * @var array
     */
    private $data = array();

    /**
     * @inheritdoc
     */
    public function set($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     * @inheritdoc
     */
    public function get($key) {
        return $this->data[$key];
    }

    /**
     * @inheritdoc
     */
    public function has($key) {
        return isset($this->data[$key]);
    }

    /**
     * @inheritdoc
     */
    public function clear($key) {
        $this->data = array();
    }
}