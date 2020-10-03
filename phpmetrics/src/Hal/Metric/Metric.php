<?php
namespace Hal\Metric;

/**
 * Interface Metric
 * @package Hal\Metric
 */
interface Metric
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function set($key, $value);

    /**
     * @param string $key
     * @return mixed
     */
    public function has($key);

    /**
     * @return array<string,mixed>
     */
    public function all();
}
