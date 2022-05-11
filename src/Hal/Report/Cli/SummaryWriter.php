<?php
declare(strict_types=1);

namespace Hal\Report\Cli;

use Hal\Report\SummaryProvider;

/**
 * Dedicated writer that defines the content to write in a CLI output when exporting the summary of the metrics.
 */
final class SummaryWriter extends SummaryProvider
{
    /**
     * Return the report of the summary, as a string for CLI output.
     *
     * @return string
     */
    public function getReport(): string
    {
        return <<<EOT
LOC
    Lines of code                               {$this->sum->loc}
    Logical lines of code                       {$this->sum->lloc}
    Comment lines of code                       {$this->sum->cloc}
    Average volume                              {$this->avg->volume}
    Average comment weight                      {$this->avg->commentWeight}
    Average intelligent content                 {$this->avg->commentWeight}
    Logical lines of code by class              $this->locByClass
    Logical lines of code by method             $this->locByMethod

Object oriented programming
    Classes                                     {$this->sum->nbClasses}
    Interface                                   {$this->sum->nbInterfaces}
    Methods                                     {$this->sum->nbMethods}
    Methods by class                            $this->methodsByClass
    Lack of cohesion of methods                 {$this->avg->lcom}
    
Coupling
    Average afferent coupling                   {$this->avg->afferentCoupling}
    Average efferent coupling                   {$this->avg->efferentCoupling}
    Average instability                         {$this->avg->instability}
    Depth of Inheritance Tree                   $this->treeInheritanceDepth
    
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
    }

    /**
     * {@inheritDoc}
     */
    public function getReportFile(): bool
    {
        return !$this->config->has('quiet');
    }
}
