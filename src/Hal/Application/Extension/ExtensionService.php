<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Extension;

use Hal\Application\Config\Configuration;
use Hal\Component\Bounds\Bounds;
use Hal\Component\Result\ResultCollection;

class ExtensionService {

    /**
     * @var Repository
     */
   private $repository;

    /**
     * ExtensionService constructor.
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param Configuration $config
     * @param ResultCollection $collection
     * @param ResultCollection $aggregatedResults
     * @param Bounds $bounds
     * @return mixed
     */
    public function receive(Configuration $config, ResultCollection $collection, ResultCollection $aggregatedResults, Bounds $bounds)
    {
        // search controller
        foreach($this->repository->all() as $item) {
            $item->receive($config, $collection, $aggregatedResults, $bounds);
        }
    }
}