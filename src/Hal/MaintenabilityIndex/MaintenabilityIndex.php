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
 *      MIwoc: Maintainability Index without comments
 *      MIcw: Maintainability Index comment weight
 *      MI: Maintainability Index = MIwoc + MIcw
 *
 *      MIwoc = 171 - 5.2 * ln(aveV) -0.23 * aveG -16.2 * ln(aveLOC)
 *      MIcw = 50 * sin(sqrt(2.4 * perCM))
 *      MI = MIwoc + MIcw
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
    public function calculate(\Hal\Halstead\Result $rHalstead, \Hal\Loc\Result $rLoc, \Hal\McCabe\Result $rMcCabe)
    {
        $result = new Result;
        $result->setMaintenabilityIndexWithoutComment(max(
             (171
             - (5.2 * \log($rHalstead->getVolume()))
             - (0.23 * $rMcCabe->getCyclomaticComplexityNumber())
             - (16.2 * \log($rLoc->getLogicalLoc()))
             ) * 100 / 171
             ,0));


        // comment weight
        if($rLoc->getLoc() > 0) {
            $CM = $rLoc->getCommentLoc() / $rLoc->getLoc();
            $result->setCommentWeight(
                50 * sin(sqrt(2.4 * $CM))
            );
        }

        return $result;
    }
}