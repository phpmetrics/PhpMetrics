<?php

namespace Hal\Application;

use Exception;
use Hal\Application\Config\ConfigException;
use Hal\Application\Config\Parser;
use Hal\Application\Config\Validator;
use Hal\Component\File\Finder;
use Hal\Component\Issue\Issuer;
use Hal\Component\Output\CliOutput;
use Hal\Metric\SearchMetric;
use Hal\Report;
use Hal\Search\PatternSearcher;
use Hal\Violation\Violation;
use Hal\Violation\ViolationParser;

class Application
{
    /**
     * @param array $argv
     */
    public function run(array $argv)
    {
        // formatter
        $output = new CliOutput();

        // issues and debug
        $issuer = (new Issuer($output));//->enable();

        // config
        $config = (new Parser())->parse($argv);

        // Help
        if ($config->has('help')) {
            $output->writeln((new Validator())->help());
            exit(0);
        }

        // Metrics list
        if ($config->has('metrics')) {
            $output->writeln((new Validator())->metrics());
            exit(0);
        }

        // Version
        if ($config->has('version')) {
            $output->writeln(sprintf(
                "PhpMetrics %s <http://www.phpmetrics.org>\nby Jean-François Lépine <https://twitter.com/Halleck45>\n",
                getVersion()
            ));
            exit(0);
        }

        try {
            (new Validator())->validate($config);
        } catch (ConfigException $e) {
            $output->writeln(sprintf("\n<error>%s</error>\n", $e->getMessage()));
            $output->writeln((new Validator())->help());
            exit(1);
        }

        if ($config->has('quiet')) {
            $output->setQuietMode(true);
        }

        // find files
        $finder = new Finder($config->get('extensions'), $config->get('exclude'));
        $files = $finder->fetch($config->get('files'));

        // analyze
        try {
            $metrics = (new Analyze($config, $output, $issuer))->run($files);
        } catch (ConfigException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            exit(1);
        }

        // search
        $searches = $config->get('searches');
        $searcher = new PatternSearcher();
        $foundSearch = new SearchMetric('searches');
        foreach ($searches->all() as $search) {
            $foundSearch->set($search->getName(), $searcher->executes($search, $metrics));
        }
        $metrics->attach($foundSearch);

        // violations
        (new ViolationParser($config, $output))->apply($metrics);

        // report
        try {
            (new Report\Cli\Reporter($config, $output))->generate($metrics);
            (new Report\Cli\SearchReporter($config, $output))->generate($metrics);
            (new Report\Html\Reporter($config, $output))->generate($metrics);
            (new Report\Csv\Reporter($config, $output))->generate($metrics);
            (new Report\Json\Reporter($config, $output))->generate($metrics);
            (new Report\Json\SummaryReporter($config, $output))->generate($metrics);
            (new Report\Violations\Xml\Reporter($config, $output))->generate($metrics);
        } catch (Exception $e) {
            $output->writeln(sprintf('<error>Cannot generate report: %s</error>', $e->getMessage()));
            $output->writeln('');
            exit(1);
        }

        // exit status
        $shouldExitDueToCriticalViolationsCount = 0;
        foreach ($metrics->all() as $metric) {
            foreach ($metric->get('violations') as $violation) {
                if (Violation::CRITICAL === $violation->getLevel()) {
                    $shouldExitDueToCriticalViolationsCount++;
                }
            }
        }
        if (!empty($shouldExitDueToCriticalViolationsCount)) {
            $output->writeln('');
            $output->writeln(sprintf(
                '<error>[ERR] Failed du to %d critical violations</error>',
                $shouldExitDueToCriticalViolationsCount
            ));
            $output->writeln('');
            exit(1);
        }

        // end
        $output->writeln('');
        $output->writeln('<success>Done</success>');
        $output->writeln('');
    }
}
