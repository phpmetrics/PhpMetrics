<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Command\Job\Analyze;

use Hal\Component\OOP\Extractor\ClassMap;
use Hal\Component\Result\ResultCollection;
use Hal\Metrics\Complexity\Structural\CardAndAgresti\FileSystemComplexity;


/**
 * Starts analyze
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class CardAndAgrestiAnalyzer
{

    /**
     * @var ClassMap
     */
    private $classMap;

    /**
     * @var ResultCollection
     */
    private $collection;

    /**
     * Constructor
     *
     * @param ClassMap $classMap
     * @param ResultCollection $collection
     */
    public function __construct(ClassMap $classMap, ResultCollection $collection)
    {
        $this->classMap = $classMap;
        $this->collection = $collection;
    }


    public function execute(array $files) {
        $object = new FileSystemComplexity($this->classMap);
        foreach($files as $filename) {
            $result = $object->calculate($filename);
            $this->collection->get($filename)->setSystemComplexity($result);
        }
    }

}
