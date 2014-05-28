<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metrics\Complexity\Component\McCabe;
use Hal\Component\Result\ExportableInterface;

/**
 * Representation of McCaybe measure
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Result implements ExportableInterface {

    /**
     * @var int
     */
    private $cyclomaticComplexityNumber;

    /**
     * @inheritdoc
     */
    public function asArray() {
        return array (
            'cyclomaticComplexity' => $this->getCyclomaticComplexityNumber()
        );
    }

    /**
     * @param int $cyclomaticComplexityNumber
     */
    public function setCyclomaticComplexityNumber($cyclomaticComplexityNumber)
    {
        $this->cyclomaticComplexityNumber = (int) $cyclomaticComplexityNumber;
    }

    /**
     * @return int
     */
    public function getCyclomaticComplexityNumber()
    {
        return $this->cyclomaticComplexityNumber;
    }
}