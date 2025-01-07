<?php
declare(strict_types=1);

namespace Hal\Report\Json;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\File\WriterInterface;
use Hal\Exception\NotWritableJsonReportException;
use Hal\Report\SummaryProvider;
use function dirname;

/**
 * Dedicated writer that defines the content to write in a JSON file when exporting the summary of the metrics.
 */
final class SummaryWriter extends SummaryProvider
{
    public function __construct(
        ConfigBagInterface $config,
        private readonly WriterInterface $fileWriter
    ) {
        parent::__construct($config);
    }

    /**
     * Return the report of the summary, into an array adapted for the JSON report format.
     *
     * @return array<string, mixed>
     */
    public function getReport(): array
    {
        return [
            'LOC' => [
                'linesOfCode' => $this->sum->loc,
                'logicalLinesOfCode' => $this->sum->lloc,
                'commentLinesOfCode' => $this->sum->cloc,
                'avgVolume' => $this->avg->volume,
                'avgCommentWeight' => $this->avg->commentWeight,
                'avgIntelligentContent' => $this->avg->intelligentContent,
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
                'inheritanceTreeDepth' => $this->treeInheritanceDepth,
            ],
            'Package' => [
                'packages' => $this->sum->nbPackages,
                'avgClassesPerPackage' => $this->avg->classesPerPackage,
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
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getReportFile(): string|false
    {
        if ($this->config->has('quiet')) {
            return false;
        }

        /** @var null|string $logFile */
        $logFile = $this->config->get('report-summary-json');
        if (null === $logFile) {
            return false;
        }

        if (!$this->fileWriter->exists(dirname($logFile)) || !$this->fileWriter->isWritable(dirname($logFile))) {
            throw NotWritableJsonReportException::noPermission($logFile);
        }

        return $logFile;
    }
}
