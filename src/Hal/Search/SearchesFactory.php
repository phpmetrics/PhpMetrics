<?php

namespace Hal\Search;

class SearchesFactory
{
    /**
     * @param array $data
     * @return Searches
     */
    public function factory(array $data)
    {
        $searches = new Searches();

        foreach ($data as $name => $search) {
            $searches->add(new Search($name, $search));
        }

        return $searches;
    }
}
