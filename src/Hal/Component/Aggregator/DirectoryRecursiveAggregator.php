<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Aggregator;
use Hal\Component\Bounds\Result\BoundsResult;
use Hal\Component\Result\ResultCollection;

/**
 * Agregates by directory
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class DirectoryRecursiveAggregator implements Aggregator {

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
        $array = new ResultCollection();
        foreach($results as $result) {
            $basename = dirname($result->getFilename());

            // from 'folder1/folder2/file.php', we want an array with ('folder1', 'folder2')
            $namespaces = explode(DIRECTORY_SEPARATOR, $basename);

            if($this->depth) {
                array_splice($namespaces, $this->depth);
            }

            // merge infos for each namespace in the DirectoryResultCollection
            $len = sizeof($namespaces, COUNT_NORMAL);

            for($i = 0; $i < $len; $i++) {

                $namespace = $namespaces[$i];
                if(0 === strlen($namespace)) {
                    $namespace = '.';
                }

                if(0 === $i) {
                    // root
                    if(!isset($array[$namespace])) {
                        $array[$namespace] = new ResultCollection();
                    }
                    $parent = &$array[$namespace];
                } else {
                    // namespace
                    if(!isset($parent[$namespace])) {
                        $parent[$namespace] = new ResultCollection(); // ResultRecursiveCollection -> has getOOP(), etc.
                    }
                    $parent = &$parent[$namespace];
                }
            }
            $parent->push($result);
        }
        return $array;
    }
}