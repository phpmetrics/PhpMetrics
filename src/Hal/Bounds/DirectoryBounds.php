<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Bounds;
use Hal\Bounds\Result\DirectoryResult;
use Hal\Result\ResultCollection;


/**
 * consolidate results by directory
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class DirectoryBounds implements BoundsInterface{

    /**
     * Depth max
     *
     * @var int
     */
    private $depth;

    /**
     * Constructor
     *
     * @param int $depth
     */
    public function __construct($depth = 0)
    {
        $this->depth = (int) $depth;
    }

    /**
     * @inheritdoc
     */
    public function calculate(ResultCollection $results) {

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

        // boundaries
        $collection = array();
        $bounds = new Bounds();
        foreach($array as $namespace => $directoryResultSets) {
            $boundsResult = $bounds->calculate($directoryResultSets);
            $collection[$namespace] = new DirectoryResult($namespace, $boundsResult);
        }
        ksort($collection);

        return $collection;
    }
}