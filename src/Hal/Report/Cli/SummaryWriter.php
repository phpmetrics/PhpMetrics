<?php

namespace Hal\Report\Cli;

use Hal\Report\SummaryProvider;

class SummaryWriter extends SummaryProvider
{
    public function getReport()
    {
        $out = <<<EOT
LOC
    Lines of code                               {$this->sum->loc}
    Logical lines of code                       {$this->sum->lloc}
    Comment lines of code                       {$this->sum->cloc}
    Average volume                              {$this->avg->volume}
    Average comment weight                      {$this->avg->commentWeight}
    Average intelligent content                 {$this->avg->commentWeight}
    Logical lines of code by class              {$this->locByClass}
    Logical lines of code by method             {$this->locByMethod}
Object oriented programming
    Classes                                     {$this->sum->nbClasses}
    Interface                                   {$this->sum->nbInterfaces}
    Methods                                     {$this->sum->nbMethods}
    Methods by class                            {$this->methodsByClass}
    Lack of cohesion of methods                 {$this->avg->lcom}

Coupling
    Average afferent coupling                   {$this->avg->afferentCoupling}
    Average efferent coupling                   {$this->avg->efferentCoupling}
    Average instability                         {$this->avg->instability}
    Depth of Inheritance Tree                   {$this->treeInheritenceDepth}

Package
    Packages                                    {$this->sum->nbPackages}
    Average classes per package                 {$this->avg->classesPerPackage}
    Average distance                            {$this->avg->distance}
    Average incoming class dependencies         {$this->avg->incomingCDep}
    Average outgoing class dependencies         {$this->avg->outgoingCDep}
    Average incoming package dependencies       {$this->avg->incomingPDep}
    Average outgoing package dependencies       {$this->avg->outgoingPDep}
Complexity
    Average Cyclomatic complexity by class      {$this->avg->ccn}
    Average Weighted method count by class      {$this->avg->wmc}
    Average Relative system complexity          {$this->avg->relativeSystemComplexity}
    Average Difficulty                          {$this->avg->difficulty}

Bugs
    Average bugs by class                       {$this->avg->bugs}
    Average defects by class (Kan)              {$this->avg->kanDefect}
Violations
    Critical                                    {$this->sum->violations->critical}
    Error                                       {$this->sum->violations->error}
    Warning                                     {$this->sum->violations->warning}
    Information                                 {$this->sum->violations->information}
EOT;

        // git
        if ($this->config->has('git')) {
            $commits = [];
            foreach ($this->consolidated->getFiles() as $name => $file) {
                $commits[$name] = $file['gitChanges'];
            }
            arsort($commits);
            $commits = array_slice($commits, 0, 10);

            $out .= "\nTop 10 committed files";
            foreach ($commits as $file => $nb) {
                $out .= sprintf("\n    %d    %s", $nb, $file);
            }
            if (0 === count($commits)) {
                $out .= "\n    NA";
            }
            $out .= "\n";
        }

        // Junit
        if ($this->config->has('junit')) {
            $out .= <<<EOT

Unit testing
    Number of unit tests                        {$this->metrics->get('unitTesting')->get('nbSuites')}
    Classes called by tests                     {$this->metrics->get('unitTesting')->get('nbCoveredClasses')}
    Classes called by tests (percent)           {$this->metrics->get('unitTesting')->get('percentCoveredClasses')} %
EOT;
        }

        $out .= "\n\n";

        return $out;
    }
}
