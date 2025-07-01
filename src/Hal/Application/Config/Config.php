<?php

namespace Hal\Application\Config;

class Config
{
    /**
     * @var array
     */
    private $bag = [];

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->bag[$key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->bag[$key]);
    }

    /**
     * @param $key
     * @return null
     */
    public function get($key)
    {
        return $this->has($key) ? $this->bag[$key] : null;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->bag;
    }

    /**
     * @param array $array
     * @return $this
     */
    public function fromArray(array $array)
    {
        foreach ($array as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }
}
