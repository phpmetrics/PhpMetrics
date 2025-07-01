<?php

namespace Hal\Metric\System\UnitTesting;

use Hal\Application\Config\Config;
use Hal\Application\Config\ConfigException;
use Hal\Component\Ast\ParserFactoryBridge;
use Hal\Component\Ast\ParserTraverserVisitorsAssigner;
use Hal\Metric\Class_\ClassEnumVisitor;
use Hal\Metric\Class_\Coupling\ExternalsVisitor;
use Hal\Metric\ClassMetric;
use Hal\Metric\Metrics;
use Hal\Metric\ProjectMetric;
use PhpParser\ParserFactory;

class UnitTesting
{
    /**
     * @var array
     */
    private $files = [];

    /**
     * @var Config
     */
    private $config;

    /**
     * @param array $files
     */
    public function __construct(Config $config, array $files)
    {
        $this->files = $files;
        $this->config = $config;
    }

    /**
     * @param Metrics $metrics
     * @throws ConfigException
     */
    public function calculate(Metrics $metrics)
    {
        if (!$this->config->has('junit')) {
            return;
        }

        // parse junit file
        $filename = $this->config->get('junit');
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new ConfigException('JUnit report cannot be read');
        }

        $unitsTests = [];
        $infoAboutTests = [];
        $assertions = 0;
        $projectMetric = new ProjectMetric('unitTesting');

        // injects default value for each metric
        foreach ($metrics->all() as $metric) {
            $metric->set('numberOfUnitTests', 0);
        }

        // parsing of XML file without any dependency to DomDocument or simpleXML
        // we want to be compatible with every platforms. Maybe (probably) that's a really stupid idea, but I want to try it :p
        $testsuites = [];
        $alreadyParsed = [];

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load($filename);
        $xpath = new \DOMXpath($dom);

        // JUNIT format
        foreach ($xpath->query('//testsuite[@file]') as $suite) {
            array_push($testsuites, (object) [
                'file' => $suite->getAttribute('file'),
                'name' => $suite->getAttribute('name'),
                'assertions' => $suite->getAttribute('assertions'),
                'time' => $suite->getAttribute('time'),
            ]);
        }

        // CODECEPTION format (file is stored in the <testcase> node
        foreach ($xpath->query('//testcase[@file]') as $index => $case) {
            $suite = $case->parentNode;

            if ($suite->hasAttribute('file')) {
                // avoid duplicates
                continue;
            }

            if (isset($testsuites[$case->getAttribute('class')])) {
                // codeception does not consider testcase like junit does
                continue;
            }

            if ($suite->hasAttribute('assertions')) {
                // codeception store assertions in testsuite, not in testcase
                // (but it stores classname in the testcase node) oO
                // so we will store "assertions" in the first testcase of the testsuite only
                $assertions = $case === $suite->firstChild->nextSibling ? $suite->getAttribute('assertions') : 0;
            }

            $testsuites[$case->getAttribute('class')] = (object) [
                'file' => $case->getAttribute('file'),
                'name' => $case->getAttribute('class'),
                'assertions' => $assertions,
                'time' => $suite->getAttribute('time'),
            ];
        }

        // analyze each unit test
        // This code is slow and can be optimized
        foreach ($testsuites as $suite) {
            $metricsOfUnitTest = new Metrics();
            $parser = (new ParserFactoryBridge())->create();
            $traverser = new \PhpParser\NodeTraverser();
            (new ParserTraverserVisitorsAssigner())->assign($traverser, [
                new \PhpParser\NodeVisitor\NameResolver(),
                new ClassEnumVisitor($metricsOfUnitTest),
                new ExternalsVisitor($metricsOfUnitTest)
            ]);

            if (!file_exists($suite->file) || !is_readable($suite->file)) {
                throw new \LogicException('Cannot find source file referenced in testsuite: ' . $suite->file);
            }

            $code = file_get_contents($suite->file);
            $stmts = $parser->parse($code);
            $traverser->traverse($stmts);

            if (!$metricsOfUnitTest->has($suite->name)) {
                continue;
            }

            // list of externals sources of unit test
            $metric = $metricsOfUnitTest->get($suite->name);
            $externals = (array) $metric->get('externals');

            // global stats for each test
            $infoAboutTests[$suite->name] = (object) [
                'nbExternals' => count(array_unique($externals)),
                'externals' => array_unique($externals),
                'filename' => $suite->file,
                'classname' => $suite->name,
                'assertions' => $suite->assertions,
                'time' => $suite->time,
            ];

            $assertions += $suite->assertions;

            foreach ($externals as $external) {
                // search for this external in metrics
                if (!$metrics->has($external)) {
                    continue;
                }

                // SUT (tested class) has unit test
                $numberOfUnitTest = $metrics->get($external)->get('numberOfUnitTests');
                $numberOfUnitTest++;
                $metrics->get($external)->set('numberOfUnitTests', $numberOfUnitTest);
            }
        }

        // statistics
        $sum = 0;
        $nb = 0;
        foreach ($metrics->all() as $metric) {
            if (!$metric instanceof ClassMetric) {
                continue;
            }

            $sum++;
            if ($metric->get('numberOfUnitTests') > 0) {
                $nb++;
            }
        }

        $projectMetric->set('assertions', $assertions);
        $projectMetric->set('tests', $infoAboutTests);
        $projectMetric->set('nbSuites', count($testsuites));
        $projectMetric->set('nbCoveredClasses', $nb);
        $projectMetric->set('percentCoveredClasses', round($nb / max($sum, 1) * 100, 2));
        $projectMetric->set('nbUncoveredClasses', $sum - $nb);
        $projectMetric->set('percentUncoveredClasses', round(($sum - $nb) / max($sum, 1) * 100, 2));

        $metrics->attach($projectMetric);
    }
}
