<?php
namespace Hal\Metric\System\UnitTesting;

use Hal\Application\Config\Config;
use Hal\Application\Config\ConfigException;
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
     * GitChanges constructor.
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
        $xml = file_get_contents($filename);
        if (preg_match_all('!<testsuite[^<]*?file="(.*?)".*?>!si', $xml, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                list($line, $fileOfUnitTest) = $m;

                $suite = new \stdClass;
                $suite->file = $fileOfUnitTest;

                $line  = str_ireplace('<testsuite ', '', $line);
                if (preg_match_all('!(.*?)="(.*?)"!s', $line, $assign, PREG_SET_ORDER)) {
                    foreach ($assign as $ass) {
                        $suite->{trim($ass[1])} = trim($ass[2]);
                    }
                }

                if (!isset($suite->name, $suite->file, $suite->assertions)) {
                    // test suite without file
                    continue;
                }

                array_push($testsuites, $suite);
            }
        }


        // analyze each unit test
        // This code is slow and can be optimized
        foreach ($testsuites as $suite) {
            $metricsOfUnitTest = new Metrics();
            $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
            $traverser = new \PhpParser\NodeTraverser();
            $traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
            $traverser->addVisitor(new ClassEnumVisitor($metricsOfUnitTest));
            $traverser->addVisitor(new ExternalsVisitor($metricsOfUnitTest));

            $code = file_get_contents($suite->file);
            $stmts = $parser->parse($code);
            $traverser->traverse($stmts);

            if (!$metricsOfUnitTest->has($suite->name)) {
                continue;
            }

            // list of externals sources of unit test
            $metric = $metricsOfUnitTest->get($suite->name);
            $externals = (array)$metric->get('externals');

            // global stats for each test
            $infoAboutTests[$suite->name] = (object)[
                'nbExternals' => sizeof(array_unique($externals)),
                'externals' => array_unique($externals),
                'filename' => $fileOfUnitTest,
                'classname' => $suite->name,
                'assertions' => $suite->assertions,
                'errors' => $suite->errors,
                'failures' => $suite->failures,
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
        $projectMetric->set('nbSuites', sizeof($testsuites));
        $projectMetric->set('nbCoveredClasses', $nb);
        $projectMetric->set('percentCoveredClasses', round($nb / max($sum, 1) * 100, 2));
        $projectMetric->set('nbUncoveredClasses', $sum - $nb);
        $projectMetric->set('percentUncoveredClasses', round(($sum - $nb) / max($sum, 1) * 100, 2));

        $metrics->attach($projectMetric);
    }
}
