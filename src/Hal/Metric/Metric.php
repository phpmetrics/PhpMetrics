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
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function set($key, $value);

    /**
     * @param $key
     * @return mixed
     */
    public function has($key);

    /**
     * @return array
     */
    public function all();
}