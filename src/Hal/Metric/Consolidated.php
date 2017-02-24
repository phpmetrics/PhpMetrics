<?php
namespace Hal\Metric;

use Hal\Violation\Violation;

class Consolidated
{
    /**
     * @var object
     */
    private $avg;

    /**
     * @var object
     */
    private $sum;

    /**
     * @var array
     */
    private $classes = [];

    /**
     * @var array
     */
    private $files = [];

    /**
     * @var array
     */
    private $project = [];

    /**
     * Consolided constructor.
     * @param Metrics $metrics
     */
    public function __construct(Metrics $metrics)
    {
        // grouping results
        $classes = [];
        $functions = [];
        $files = [];
        $project = [];
        $nbInterfaces = 0;
        foreach ($metrics->all() as $key => $item) {
            $classItem = get_class($item);
            if (ClassMetric::class === $classItem) {
                $classes[] = $item->all();
            } elseif (InterfaceMetric::class === $classItem) {
                $nbInterfaces++;
            } elseif (FunctionMetric::class === $classItem) {
                $functions[$key] = $item->all();
            } elseif (FileMetric::class === $classItem) {
                $files[$key] = $item->all();
            } elseif (ProjectMetric::class === $classItem) {
                $project[$key] = $item->all();
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
            'mi' => [],
        ];

        foreach ($metrics->all() as $key => $item) {
            $sum->loc += $item->get('loc');
            $sum->lloc += $item->get('lloc');
            $sum->cloc += $item->get('cloc');
            $sum->nbMethods += $item->get('nbMethods');

            foreach ($avg as $k => $a) {
                array_push($avg->$k, $item->get($k));
            }
        }
        $sum->nbClasses = count($classes);
        $sum->nbInterfaces = $nbInterfaces;

        foreach ($avg as &$a) {
            if (sizeof($a) > 0) {
                $a = round(array_sum($a) / sizeof($a), 2);
            } else {
                $a = 0;
            }
        }

        // sums of violations
        $violations = [
            'total' => 0,
            'information' => 0,
            'warning' => 0,
            'error' => 0,
            'critical' => 0,
        ];
        $map = [
            Violation::INFO => 'information',
            Violation::WARNING => 'warning',
            Violation::ERROR => 'error',
            Violation::CRITICAL => 'critical',
        ];
        foreach ($classes as $class) {
            foreach ($class['violations'] as $violation) {
                $violations['total']++;
                $name = $map[$violation->getLevel()];
                $violations[$name]++;
            }
        }
        $sum->violations = (object)$violations;


        $this->avg = $avg;
        $this->sum = $sum;
        $this->classes = $classes;
        $this->files = $files;
        $this->project = $project;
    }

    /**
     * @return object
     */
    public function getAvg()
    {
        return $this->avg;
    }

    /**
     * @return object
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * @return array
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return array
     */
    public function getProject()
    {
        return $this->project;
    }
}
