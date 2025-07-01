<?php

namespace Hal\Search;

class Searches
{
    /**
     * @var array
     */
    private $searches = [];

    /**
     * @param Search $search
     * @return $this
     */
    public function add(Search $search)
    {
        $this->searches[$search->getName()] = $search;
        return $this;
    }

    /**
     * @param $name
     * @return Search|null
     */
    public function get($name)
    {
        return $this->has($name) ? $this->searches[$name] : null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->searches[$name]);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->searches;
    }
}
