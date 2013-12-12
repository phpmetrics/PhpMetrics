<?php
namespace MaintenabilityIndex;

class MaintenabilityIndex {

    public function calculate(\Halstead\Result $rHalstead, \Loc\Result $rLoc)
    {
        $result = new Result;
        $result->setMaintenabilityIndex(
            171
            - (3.42 * \log($rHalstead->getEffort()))
            - (0.23 * \log($rLoc->getComplexityCyclomatic()))
            - (16.2 * \log($rLoc->getLogicalLoc()))
        );
        return $result;
    }
}