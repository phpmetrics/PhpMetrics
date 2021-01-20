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

    /** @var array */
    private $packages;

    /**
     * @param Metrics $metrics
     */
    public function __construct(Metrics $metrics)
    {
        $classMetrics = [];

        // grouping results
        $classes = [];
        $functions = [];
        $files = [];
        $packages = [];
        $project = [];
        $nbInterfaces = 0;
        foreach ($metrics->all() as $key => $item) {
            $classItem = get_class($item);
            if (ClassMetric::class === $classItem) {
                $classes[] = $item->all();
                $classMetrics[] = $item;
            } elseif (InterfaceMetric::class === $classItem) {
                $nbInterfaces++;
            } elseif (FunctionMetric::class === $classItem) {
                $functions[$key] = $item->all();
            } elseif (FileMetric::class === $classItem) {
                $files[$key] = $item->all();
            } elseif (ProjectMetric::class === $classItem) {
                $project[$key] = $item->all();
            } elseif (PackageMetric::class === $classItem) {
                $packages[$key] = $item->all();
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
            'wmc' => [],
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

        foreach ($classMetrics as $key => $item) {
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
        $sum->nbPackages = count($packages);

        foreach ($avg as &$a) {
            if (count($a) > 0) {
                $a = round(array_sum((array)$a) / count($a), 2);
            } else {
                $a = 0;
            }
        }

        $avg->distance = 0;
        $avg->incomingCDep = 0;
        $avg->incomingPDep = 0;
        $avg->outgoingCDep = 0;
        $avg->outgoingPDep = 0;
        $avg->classesPerPackage = 0;
        if (0 !== $sum->nbPackages) {
            foreach (array_keys($packages) as $eachName) {
                /* @var $eachPackage PackageMetric */
                $eachPackage = $metrics->get($eachName);
                $avg->distance += $eachPackage->getDistance();
                $avg->incomingCDep += count($eachPackage->getIncomingClassDependencies());
                $avg->incomingPDep += count($eachPackage->getIncomingPackageDependencies());
                $avg->outgoingCDep += count($eachPackage->getOutgoingClassDependencies());
                $avg->outgoingPDep += count($eachPackage->getOutgoingPackageDependencies());
                $avg->classesPerPackage += count($eachPackage->getClasses());
            }
            $avg->distance = round($avg->distance / $sum->nbPackages, 2);
            $avg->incomingCDep = round($avg->incomingCDep / $sum->nbPackages, 2);
            $avg->incomingPDep = round($avg->incomingPDep / $sum->nbPackages, 2);
            $avg->outgoingCDep = round($avg->outgoingCDep / $sum->nbPackages, 2);
            $avg->outgoingPDep = round($avg->outgoingPDep / $sum->nbPackages, 2);
            $avg->classesPerPackage = round($avg->classesPerPackage / $sum->nbPackages, 2);
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
        foreach ($packages as $package) {
            foreach ($package['violations'] as $violation) {
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
        $this->packages = $packages;
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

    /**
     * @return array
     */
    public function getPackages()
    {
        return $this->packages;
    }
}
