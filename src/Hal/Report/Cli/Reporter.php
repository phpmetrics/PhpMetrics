<?php
namespace Hal\Report\Cli;

use Hal\Application\Config\Config;
use Hal\Metric\ClassMetric;
use Hal\Metric\Consolidated;
use Hal\Metric\FunctionMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metrics;
use Symfony\Component\Console\Output\OutputInterface;

class Reporter
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Reporter constructor.
     * @param Config $config
     * @param OutputInterface $output
     */
    public function __construct(Config $config, OutputInterface $output)
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
    Average afferent coupling                   {$avg->afferentCoupling}
    Average efferent coupling                   {$avg->efferentCoupling}
    Average instability                         {$avg->instability}

Complexity
    Average Cyclomatic complexity by class      {$avg->ccn}
    Average Relative system complexity          {$avg->relativeSystemComplexity}
    Average Difficulty                          {$avg->difficulty}
    
Bugs
    Average bugs by class                       {$avg->bugs}
    Average defects by class (Kan)              {$avg->kanDefect}


EOT;

        // git
        if($this->config->has('git')) {
            $commits = [];
            foreach($consolidated->getFiles() as $name => $file) {
                $commits[$name] = $file['gitChanges'];
            }
            arsort($commits);
            $commits = array_slice($commits, 0, 10);

            $out .= "\nTop 10 commited files";
            foreach($commits as $file => $nb) {
                $out .= sprintf("\n    %d    %s", $nb, $file);
            }
            if(0 === sizeof($commits)) {
                $out .= "\n    NA";
            }
        }

        $out .= "\n\n";
        $this->output->write($out);

    }

}
