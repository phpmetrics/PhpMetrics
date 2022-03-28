<?php
declare(strict_types=1);

namespace Hal\Metric\System\UnitTesting;

use DOMDocument;
use DOMXPath;
use Hal\Application\Config\ConfigBagInterface;
use Hal\Application\Workflow\Task\PrepareParserTask;
use Hal\Application\Workflow\Task\WorkflowTaskInterface;
use Hal\Component\Output\CliOutput;
use Hal\Exception\UnreadableJUnitFileException;
use Hal\Metric\CalculableInterface;
use Hal\Metric\Class_\Coupling\ExternalsVisitor;
use Hal\Metric\ClassMetric;
use Hal\Metric\Helper\RegisterMetricsVisitor;
use Hal\Metric\Helper\SimpleNodeIterator;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use Hal\Metric\ProjectMetric;
use LogicException;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use stdClass;
use function array_filter;
use function array_key_exists;
use function array_unique;
use function is_readable;
use function max;
use function round;

/**
 * This class calculates some PHP Unit stats using a given JUnit report.
 */
final class UnitTesting implements CalculableInterface
{
    /**
     * @param Metrics $metrics
     * @param ConfigBagInterface $config
     */
    public function __construct(
        private readonly Metrics $metrics,
        private readonly ConfigBagInterface $config
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(): void
    {
        if (!$this->config->has('junit')) {
            return;
        }

        // Read junit file.
        $filename = $this->config->get('junit');
        if (!is_readable($filename)) {
            throw UnreadableJUnitFileException::noPermission($filename);
        }

        $infoAboutTests = [];
        $assertions = 0;
        $projectMetric = new ProjectMetric('unitTesting');

        // Injects default value for each class metric.
        foreach ($this->metrics->getClassMetrics() as $metric) {
            $metric->set('numberOfUnitTests', 0);
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->load($filename);
        $xpath = new DOMXpath($dom);

        $testSuites = [...$this->parseJUnitFile($xpath), ...$this->parseCodeceptionFile($xpath)];

        // analyze each unit test
        // This code is slow and can be optimized
        foreach ($testSuites as $suite) {
            if (!is_readable($suite->file)) {
                // TODO: specific exception.
                throw new LogicException('Cannot read source file referenced in testsuite: ' . $suite->file);
            }

            $unitTestMetrics = new Metrics();
            $this->initSpecificNodeTraverserWithMetrics($unitTestMetrics)->process([$suite->file]);
            if (!$unitTestMetrics->has($suite->name)) {
                continue;
            }

            // list of externals sources of unit test
            $externals = (array)$unitTestMetrics->get($suite->name)?->get('externals');
            $uniqueExternals = array_unique($externals);

            // Global stats for each test.
            $infoAboutTests[$suite->name] = (object)[
                'nbExternals' => count($uniqueExternals),
                'externals' => $uniqueExternals,
                'filename' => $suite->file,
                'classname' => $suite->name,
                'assertions' => $suite->assertions,
                'time' => $suite->time,
            ];

            $assertions += $suite->assertions;

            foreach ($externals as $external) {
                // Search for this external in metrics.
                if (!$this->metrics->has($external)) {
                    continue;
                }
                /** @var Metric $externalMetric */
                $externalMetric = $this->metrics->get($external);
                $externalMetric->set('numberOfUnitTests', $externalMetric->get('numberOfUnitTests') + 1);
            }
        }

        $classes = $this->metrics->getClassMetrics();
        $nbClasses = count($classes);
        $testedClasses = array_filter($classes, static function (ClassMetric $metric): bool {
            return $metric->get('numberOfUnitTests') > 0;
        });
        $nbTestedClasses = count($testedClasses);
        $nbUncoveredClasses = $nbClasses - $nbTestedClasses;

        $projectMetric->set('assertions', $assertions);
        $projectMetric->set('tests', $infoAboutTests);
        $projectMetric->set('nbSuites', count($testSuites));
        $projectMetric->set('nbCoveredClasses', $nbTestedClasses);
        $projectMetric->set('nbUncoveredClasses', $nbUncoveredClasses);
        $projectMetric->set('percentCoveredClasses', round($nbTestedClasses / max($nbClasses, 1) * 100, 2));
        $projectMetric->set('percentUncoveredClasses', round($nbUncoveredClasses / max($nbClasses, 1) * 100, 2));

        $this->metrics->attach($projectMetric);
    }

    /**
     * Parses a JUnit file and return related testSuites information.
     *
     * @param DOMXPath $xPath
     * @return array<stdClass>
     */
    private function parseJUnitFile(DOMXPath $xPath): array
    {
        $testSuites = [];
        foreach ($xPath->query('//testsuite[@file]') as $suite) {
            $testSuites[] = (object)[
                'file' => $suite->getAttribute('file'),
                'name' => $suite->getAttribute('name'),
                'assertions' => $suite->getAttribute('assertions'),
                'time' => $suite->getAttribute('time'),
            ];
        }
        return $testSuites;
    }

    /**
     * Parses a Codeception file and return related testSuites information.
     *
     * @param DOMXPath $xPath
     * @return array<stdClass>
     */
    private function parseCodeceptionFile(DOMXPath $xPath): array
    {
        $testSuites = [];
        foreach ($xPath->query('//testcase[@file]') as $case) {
            $suite = $case->parentNode;
            $class = $case->getAttribute('class');

            // Ignore duplicates by files or testcases.
            if (array_key_exists($class, $testSuites) || $suite->hasAttribute('file')) {
                continue;
            }

            $assertions = 0;
            if ($suite->hasAttribute('assertions')) {
                // Codeception stores assertions in testsuite, not in testcase, but it stores classname in the testcase
                // node, so we will store "assertions" in the first testcase of the testsuite only.
                $assertions = $case === $suite->firstChild->nextSibling ? $suite->getAttribute('assertions') : 0;
            }

            $testSuites[$class] = (object)[
                'file' => $case->getAttribute('file'),
                'name' => $class,
                'assertions' => $assertions,
                'time' => $suite->getAttribute('time'),
            ];
        }
        return $testSuites;
    }

    /**
     * Initialize a specific node traverser only for the unit tests results.
     * TODO: There is no Dependency Injection here, and testing this will be painful. Refactor.
     *
     * @param Metrics $unitTestMetrics
     * @return WorkflowTaskInterface
     */
    private function initSpecificNodeTraverserWithMetrics(Metrics $unitTestMetrics): WorkflowTaskInterface
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $traverser->addVisitor(new RegisterMetricsVisitor($unitTestMetrics));
        $traverser->addVisitor(new ExternalsVisitor($unitTestMetrics, new SimpleNodeIterator()));

        return new PrepareParserTask(
            (new ParserFactory())->create(ParserFactory::PREFER_PHP7),
            $traverser,
            new CliOutput()
        );
    }
}
