<?php
namespace Hal\Report\Cli;

use Hal\Application\Config\Config;
use Hal\Metric\ClassMetric;
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
        $classes = [];
        $functions = [];
        $nbInterfaces = 0;
        foreach ($metrics->all() as $key => $item) {
            if ($item instanceof ClassMetric) {
                $classes[] = $item->all();;
            }
            if ($item instanceof InterfaceMetric) {
                $nbInterfaces++;
            }
            if ($item instanceof FunctionMetric) {
                $functions[$key] = $item->all();;
            }
        }

        // sums
        $sum = (object)[
            'loc' => 0,
            'cloc' => 0,
            'lloc' => 0,
            'nbMethods' => 0,
        ];
        $avg = (object)[
            'ccn' => [],
            'bugs' => [],
            'kanDefect' => [],
            'relativeSystemComplexity' => [],
            'relativeDataComplexity' => [],
            'relativeStructuralComplexity' => [],
            'volume' => [],
            'commentWeight' => [],
            'intelligentContent' => [],
            'lcom' => [],
            'instability' => [],
            'afferentCoupling' => [],
            'efferentCoupling' => [],
            'difficulty' => [],
        ];
        foreach ($metrics->all() as $key => $item) {
            $sum->loc += $item->get('loc');
            $sum->lloc += $item->get('lloc');
            $sum->cloc += $item->get('cloc');
            $sum->nbMethods += $item->get('nbMethods');

            foreach ($avg as $k => &$a) {
                array_push($avg->$k, $item->get($k));
            }
        }
        $sum->nbClasses = sizeof($classes) - $nbInterfaces;
        $sum->nbInterfaces = $nbInterfaces;

        foreach ($avg as &$a) {
            if (sizeof($a) > 0) {
                $a = round(array_sum($a) / sizeof($a), 2);
            } else {
                $a = 0;
            }
        }

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


        $this->output->write($out);

    }

}
