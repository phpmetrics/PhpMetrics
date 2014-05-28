<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Mood\Abstractness;
use Hal\Component\Result\ExportableInterface;


/**
 * Represents abstractness
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Result implements ExportableInterface {

    /**
     * @var int
     */
    private $abstractness;

    /**
     * @inheritdoc
     */
    public function asArray()
    {
        return array(
            'abstractness' => $this->getAbstractness()
        );
    }

    /**
     * @param int $abstractness
     */
    public function setAbstractness($abstractness)
    {
        $this->abstractness = $abstractness;
    }

    /**
     * @return int
     */
    public function getAbstractness()
    {
        return $this->abstractness;
    }

}