<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Aggregator;
use Hal\Component\Result\ResultCollection;

/**
 * Agregates by directory. Each element is repeated for each namespace
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class DirectoryAggregatorFlat implements Aggregator {

    /**
     * Max depth
     *
     * @var int
     */
    private $depth;

    /**
     * Constructor
     *
     * @param $depth
     */
    public function __construct($depth)
    {
        $this->depth = $depth;
    }


    /**
     * @inheritdoc
     */
    public function aggregates(ResultCollection $results) {
        $array = array();
        foreach($results as $result) {
            $basename = dirname($result->getFilename());

            // from 'folder1/folder2/file.php', we want an array with ('folder1', 'folder1/folder2')
            $namespaces = array_reduce(explode(DIRECTORY_SEPARATOR, $basename), function($v, $e) {
                array_push($v, ltrim(end($v).DIRECTORY_SEPARATOR.$e, DIRECTORY_SEPARATOR));
                return $v;
            }, array());

            if($this->depth) {
                array_splice($namespaces, $this->depth);
            }

            // merge infos for each namespace in the DirectoryResultCollection
            foreach($namespaces as $namespace) {

                if(!isset($array[$namespace])) {
                    $array[$namespace] = new ResultCollection();
                }

                $array[$namespace]->push($result);
            }
        }
        return $array;
    }
}