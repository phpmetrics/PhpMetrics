<?php

namespace Hal\Metric;

/**
 * @package Hal\Metric
 */
class Metrics implements \JsonSerializable
{

    /**
     * @var array<string,Metric>
     */
    private $data = [];

    /**
     * @param Metric $metric
     * @return static
     */
    public function attach($metric)
    {
        $this->data[$metric->getName()] = $metric;
        return $this;
    }

    /**
     * @param string $key
     * @return Metric|null
     */
    public function get($key)
    {
        return $this->has($key) ? $this->data[$key] : null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * @return Metric[]
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return $this->all();
    }
}
