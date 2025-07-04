<?php

namespace Hal\Report\Json;

use Hal\Report\SummaryProvider;

class SummaryWriter extends SummaryProvider
{
    public function getReport()
    {
        return [
            'LOC' => [
                'linesOfCode' => $this->sum->loc,
                'logicalLinesOfCode' => $this->sum->lloc,
                'commentLinesOfCode' => $this->sum->cloc,
                'avgVolume' => $this->avg->volume,
                'avgCommentWeight' => $this->avg->commentWeight,
                'avgIntelligentContent' => $this->avg->commentWeight,
                'logicalLinesByClass' => $this->locByClass,
                'logicalLinesByMethod' => $this->locByMethod,
            ],
            'OOP' => [
                'classes' => $this->sum->nbClasses,
                'interface' => $this->sum->nbInterfaces,
                'methods' => $this->sum->nbMethods,
                'methodsByClass' => $this->methodsByClass,
                'lackCohesionOfMethods' => $this->avg->lcom,
            ],
            'Coupling' => [
                'avgAfferentCoupling' => $this->avg->afferentCoupling,
                'avgEfferentCoupling' => $this->avg->efferentCoupling,
                'avgInstability' => $this->avg->instability,
                'inheritanceTreeDepth' => $this->treeInheritenceDepth,
            ],
            'Package' => [
                'packages' => $this->sum->nbPackages,
                'acgClassesPerPackage' => $this->avg->classesPerPackage,
                'avgDistance' => $this->avg->distance,
                'avgIncomingClassDependencies' => $this->avg->incomingCDep,
                'avgOutgoingClassDependencies' => $this->avg->outgoingCDep,
                'avgIncomingPackageDependencies' => $this->avg->incomingPDep,
                'avgOutgoingPackageDependencies' => $this->avg->outgoingPDep,
            ],
            'Complexity' => [
                'avgCyclomaticComplexityByClass' => $this->avg->ccn,
                'avgWeightedMethodCountByClass' => $this->avg->wmc,
                'avgRelativeSystemComplexity' => $this->avg->relativeSystemComplexity,
                'avgDifficulty' => $this->avg->difficulty,
            ],
            'Bugs' => [
                'avgBugsByClass' => $this->avg->bugs,
                'avgDefectsByClass' => $this->avg->kanDefect,
            ],
            'Violations' => [
                'critical' => $this->sum->violations->critical,
                'error' => $this->sum->violations->error,
                'warning' => $this->sum->violations->warning,
                'information' => $this->sum->violations->information,
            ],
        ];
    }
}
