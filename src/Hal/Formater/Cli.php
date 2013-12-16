<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Formater;
use Hal\Result\ResultSet;


/**
 * Formater for cli usage
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Cli implements FormaterInterface {

    /**
     * @inheritdoc
     */
    public function pushResult(ResultSet $resultSet) {

        echo sprintf("\n%s:\n", $resultSet->getFilename());

        $rHalstead = $resultSet->getHalstead();
        echo "\tHalstead:\n";
        echo ''
            . sprintf("\t\tVolume: %s\n", round($rHalstead->getVolume(), 2))
            . sprintf("\t\tLength: %s\n", $rHalstead->getLength())
            . sprintf("\t\tVocabulary: %s\n", $rHalstead->getVocabulary())
            . sprintf("\t\tEffort: %d\n", $rHalstead->getEffort())
            . sprintf("\t\tDifficulty: %s\n", round($rHalstead->getDifficulty(),2 ))
            . sprintf("\t\tDelivred Bugs: %s\n", round($rHalstead->getBugs(),2 ))
            . sprintf("\t\tTime: %s\n", round($rHalstead->getTime(),2 ))
        ;

        echo "\tLOC:\n";
        $rLoc = $resultSet->getLoc();
        echo ''
            . sprintf("\t\tLOC: %s\n", $rLoc->getLoc())
            . sprintf("\t\tLogical LOC: %s\n", $rLoc->getLogicalLoc())
            . sprintf("\t\tCyclomatic complexity: %s\n", $rLoc->getComplexityCyclomatic())
        ;

        echo "\tMaintenability:\n";
        $rMaintenability = $resultSet->getMaintenabilityIndex();
        echo ''
            . sprintf("\t\tMaintenability Index: %s\n", round($rMaintenability->getMaintenabilityIndex(),2))
        ;
    }

    /**
     * @inheritdoc
     */
    public function terminate(){

    }
}