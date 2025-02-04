<?php
declare(strict_types=1);

namespace Hal\Report\OpenMetrics;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\File\WriterInterface;
use Hal\Exception\NotWritableOpenMetricsReportException;
use Hal\Report\SummaryProvider;
use OpenMetricsPhp\Exposition\Text\Collections\GaugeCollection;
use OpenMetricsPhp\Exposition\Text\Metrics\Gauge;
use OpenMetricsPhp\Exposition\Text\Types\MetricName;

use function dirname;
use function implode;

/**
 * Dedicated writer that defines the content to write in a file when exporting the OpenMetrics summary of the metrics.
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
     * Return the report of the summary, as a string for the OpenMetrics report format.
     *
     * @return string
     */
    public function getReport(): string
    {
        $reportMetrics = [
            // LOC
            'lines_of_code' => [$this->sum->loc, 'Lines of code'],
            'logical_lines_of_code' => [$this->sum->lloc, 'Logical lines of code'],
            'comment_lines_of_code' => [$this->sum->cloc, 'Comment lines of code'],
            'average_volume' => [$this->avg->volume, 'Average volume'],
            'average_comment_weight' => [$this->avg->commentWeight, 'Average comment weight'],
            'average_intelligent_content' => [$this->avg->intelligentContent, 'Average intelligent content'],
            'loc_by_class' => [$this->locByClass, 'Logical lines of code by class'],
            'loc_by_method' => [$this->locByMethod, 'Logical lines of code by method'],
            // Object oriented programming
            'number_of_classes' => [$this->sum->nbClasses, 'Number of classes'],
            'number_of_interfaces' => [$this->sum->nbInterfaces, 'Number of interfaces'],
            'number_of_methods' => [$this->sum->nbMethods, 'Number of methods'],
            'methods_by_class' => [$this->methodsByClass, 'Methods by class'],
            'lack_cohesion_methods' => [$this->avg->lcom, 'Lack of cohesion of methods'],
            // Coupling
            'afferent_coupling' => [$this->avg->afferentCoupling, 'Average afferent coupling'],
            'efferent_coupling' => [$this->avg->efferentCoupling, 'Average efferent coupling'],
            'instability' => [$this->avg->instability, 'Average instability'],
            'tree_inheritance_depth' => [$this->treeInheritanceDepth, 'Depth of inheritance tree'],
            // Package
            'number_of_packages' => [$this->sum->nbPackages, 'Number of packages'],
            'classes_by_package' => [$this->avg->classesPerPackage, 'Average classes by package'],
            'distance' => [$this->avg->distance, 'Average distance'],
            'incoming_class_dependencies' => [$this->avg->incomingCDep, 'Average incoming class dependencies'],
            'outgoing_class_dependencies' => [$this->avg->outgoingCDep, 'Average outgoing class dependencies'],
            'incoming_package_dependencies' => [$this->avg->incomingPDep, 'Average incoming package dependencies'],
            'outgoing_package_dependencies' => [$this->avg->outgoingPDep, 'Average outgoing package dependencies'],
            // Complexity
            'cyclomatic_complexity_by_class' => [$this->avg->ccn, 'Average cyclomatic complexity by class'],
            'weighted_method_count_by_class' => [$this->avg->wmc, 'Average weighted method count by class'],
            'relative_system_complexity' => [
                $this->avg->relativeSystemComplexity,
                'Average relative system complexity'
            ],
            'difficulty' => [$this->avg->difficulty, 'Average difficulty'],
            // Bugs
            'bugs_by_class' => [$this->avg->bugs, 'Average bugs by class'],
            'defects_by_class' => [$this->avg->kanDefect, 'Average defects by class (Kan)'],
            // Violations
            'critical_violations' => [$this->sum->violations->critical, 'Critical violations'],
            'error_violations' => [$this->sum->violations->error, 'Error violations'],
            'warning_violations' => [$this->sum->violations->warning, 'Warning violations'],
            'information_violations' => [$this->sum->violations->information, 'Information violations']
        ];

        $reportStrings = [];

        foreach ($reportMetrics as $metricName => $reportMetric) {
            $reportStrings[] = GaugeCollection::fromGauges(
                MetricName::fromString($metricName),
                Gauge::fromValue($reportMetric[0])
            )->withHelp($reportMetric[1])->getMetricsString();
        }

        return implode("\n", $reportStrings) . "\n# EOF\n";
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
        $logFile = $this->config->get('report-openmetrics');
        if (null === $logFile) {
            return false;
        }

        if (!$this->fileWriter->exists(dirname($logFile)) || !$this->fileWriter->isWritable(dirname($logFile))) {
            throw NotWritableOpenMetricsReportException::noPermission($logFile);
        }

        return $logFile;
    }
}
