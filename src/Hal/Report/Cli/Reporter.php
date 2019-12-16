<?php
namespace Hal\Report\Cli;

use Hal\Application\Config\Config;
use Hal\Component\Output\Output;
use Hal\Metric\Consolidated;
use Hal\Metric\Metrics;

class Reporter
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Output
     */
    private $output;

    /**
     * @param Config $config
     * @param Output $output
     */
    public function __construct(Config $config, Output $output)
    {
        $this->config = $config;
        $this->output = $output;
    }


    public function generate(Metrics $metrics)
    {
        if ($this->config->has('quiet')) {
            return;
        }

        // grouping results
        $consolidated = new Consolidated($metrics);
        $sum = $consolidated->getSum();
        $avg = $consolidated->getAvg();

        $methodsByClass = $locByClass = $locByMethod = 0;
        if ($sum->nbClasses > 0) {
            $methodsByClass = round($sum->nbMethods / $sum->nbClasses, 2);
            $locByClass = round($sum->lloc / $sum->nbClasses);
        }
        if ($sum->nbMethods > 0) {
            $locByMethod = round($sum->lloc / $sum->nbMethods);
        }

        $out = <<<EOT
LOC
    Lines of code                               {$sum->loc}
    Logical lines of code                       {$sum->lloc}
    Comment lines of code                       {$sum->cloc}
    Average volume                              {$avg->volume}
    Average comment weight                      {$avg->commentWeight}
    Average intelligent content                 {$avg->commentWeight}
    Logical lines of code by class              {$locByClass}
    Logical lines of code by method             {$locByMethod}

Object oriented programming
    Classes                                     {$sum->nbClasses}
    Interface                                   {$sum->nbInterfaces}
    Methods                                     {$sum->nbMethods}
    Methods by class                            {$methodsByClass}
    Lack of cohesion of methods                 {$avg->lcom}
    
Coupling
    Average afferent coupling                   {$avg->afferentCoupling}
    Average efferent coupling                   {$avg->efferentCoupling}
    Average instability                         {$avg->instability}
    Depth of Inheritance Tree                   {$metrics->get('tree')->get('depthOfInheritanceTree')}
    
Package
    Packages                                    {$sum->nbPackages}
    Average classes per package                 {$avg->classesPerPackage}
    Average distance                            {$avg->distance}
    Average incoming class dependencies         {$avg->incomingCDep}
    Average outgoing class dependencies         {$avg->outgoingCDep}
    Average incoming package dependencies       {$avg->incomingPDep}
    Average outgoing package dependencies       {$avg->outgoingPDep}

Complexity
    Average Cyclomatic complexity by class      {$avg->ccn}
    Average Weighted method count by class      {$avg->wmc}
    Average Relative system complexity          {$avg->relativeSystemComplexity}
    Average Difficulty                          {$avg->difficulty}
    
Bugs
    Average bugs by class                       {$avg->bugs}
    Average defects by class (Kan)              {$avg->kanDefect}

Violations
    Critical                                    {$sum->violations->critical}
    Error                                       {$sum->violations->error}
    Warning                                     {$sum->violations->warning}
    Information                                 {$sum->violations->information}

EOT;

        // git
        if ($this->config->has('git')) {
            $commits = [];
            foreach ($consolidated->getFiles() as $name => $file) {
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
    Number of unit tests                        {$metrics->get('unitTesting')->get('nbSuites')}
    Classes called by tests                     {$metrics->get('unitTesting')->get('nbCoveredClasses')}
    Classes called by tests (percent)           {$metrics->get('unitTesting')->get('percentCoveredClasses')} %
EOT;
        }

        $out .= "\n\n";
        $this->output->write($out);
    }
}
