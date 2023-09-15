<?php
declare(strict_types=1);

namespace Hal\Metric;

use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;
use stdClass;
use function array_keys;
use function array_map;
use function array_sum;
use function get_class;
use function get_object_vars;
use function round;

/**
 * Responsible for the grouping of all metrics to have a sum and average version of each of them.
 */
final class Consolidated
{
    private stdClass $avg;
    private stdClass $sum;
    /** @var array<int, array<string, mixed>> */
    private array $classes;
    /** @var array<string, array<string, mixed>> */
    private array $files;
    /** @var array<string, array<string, mixed>> */
    private array $project;
    /** @var array<string, array<string, mixed>> */
    private array $packages;

    /**
     * @param Metrics $metrics
     */
    public function __construct(Metrics $metrics)
    {
        $classMetrics = [];

        // Grouping results.
        $classes = [];
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

        foreach ($classMetrics as $item) {
            $sum->loc += $item->get('loc');
            $sum->lloc += $item->get('lloc');
            $sum->cloc += $item->get('cloc');
            $sum->nbMethods += $item->get('nbMethods');

            foreach (get_object_vars($avg) as $k => $a) {
                $avg->$k[] = $item->get($k);
            }
        }
        $sum->nbClasses = count($classes);
        $sum->nbInterfaces = $nbInterfaces;
        $sum->nbPackages = count($packages);

        foreach (get_object_vars($avg) as $k => $a) {
            /** @var array<mixed> $a */
            $avg->$k = ([] !== $a) ? round(array_sum($a) / count($a), 2) : 0;
        }

        $avg->distance = 0;
        $avg->incomingCDep = 0;
        $avg->incomingPDep = 0;
        $avg->outgoingCDep = 0;
        $avg->outgoingPDep = 0;
        $avg->classesPerPackage = 0;
        if (0 !== $sum->nbPackages) {
            foreach (array_keys($packages) as $eachName) {
                /** @var PackageMetric $eachPackage */
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
        $violationCounter = static function (array $elementViolations) use (&$violations): void {
            array_map(static function (Violation $violation) use (&$violations): void {
                $violations['total']++;
                $name = [
                    Violation::INFO => 'information',
                    Violation::WARNING => 'warning',
                    Violation::ERROR => 'error',
                    Violation::CRITICAL => 'critical',
                ][$violation->getLevel()];
                $violations[$name]++;
            }, $elementViolations);
        };

        foreach ([...$classes, ...$packages] as $element) {
            /** @var ViolationsHandlerInterface $violationHandler */
            $violationHandler = $element['violations'];
            $violationCounter($violationHandler->getAll());
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
     * @return stdClass
     */
    public function getAvg(): stdClass
    {
        return $this->avg;
    }

    /**
     * @return stdClass
     */
    public function getSum(): stdClass
    {
        return $this->sum;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getProject(): array
    {
        return $this->project;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getPackages(): array
    {
        return $this->packages;
    }
}
