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

        // I've change the original formula to adapt it to PHP projects
        // If you want try with the original measure: uncomment this code:
        //
        // $result->setMaintenabilityIndex(max(
        //     171
        //     - (5.2 * \log($rHalstead->getVolume(),2))
        //     - (0.23 * $rLoc->getComplexityCyclomatic())
        //     - (16.2 * \log($rLoc->getLogicalLoc(),2))
        //     ,0));

        $result->setMaintenabilityIndex(max(
            171
            - (1.2 * \log($rHalstead->getVolume(),2))
            - (0.23 * $rLoc->getComplexityCyclomatic())
            - (15.2 * \log($rLoc->getLogicalLoc(),2))
        ,0));
        return $result;
    }
}