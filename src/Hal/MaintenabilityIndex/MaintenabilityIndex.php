<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\MaintenabilityIndex;

/**
 * Calculates Maintenability Index
 *
 *      According to Wikipedia, "Maintainability Index is a software metric which measures how maintainable (easy to
 *      support and change) the source code is. The maintainability index is calculated as a factored formula consisting
 *      of Lines Of Code, Cyclomatic Complexity and Halstead volume."
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class MaintenabilityIndex {

    /**
     * Calculates Maintenability Index
     *
     * @param \Hal\Halstead\Result $rHalstead
     * @param \Hal\Loc\Result $rLoc
     * @return Result
     */
    public function calculate(\Hal\Halstead\Result $rHalstead, \Hal\Loc\Result $rLoc)
    {
        $result = new Result;
        $result->setMaintenabilityIndex(
            171
            - (5.2 * \log($rHalstead->getEffort()))
            - (0.23 * \log($rLoc->getComplexityCyclomatic()))
            - (16.2 * \log($rLoc->getLogicalLoc()))
        );
        return $result;
    }
}