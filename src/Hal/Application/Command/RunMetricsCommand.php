<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Command;
use Hal\Application\Command\Job\DoAggregatedAnalyze;
use Hal\Application\Command\Job\DoAnalyze;
use Hal\Application\Command\Job\Queue;
use Hal\Application\Command\Job\ReportRenderer;
use Hal\Application\Command\Job\ReportWriter;
use Hal\Application\Command\Job\SearchBounds;
use Hal\Application\Config\ConfigFactory;
use Hal\Application\Config\Logging;
use Hal\Application\Formater\Details;
use Hal\Application\Formater\Summary;
use Hal\Application\Formater\Violations\Xml;
use Hal\Component\Aggregator\DirectoryAggregatorFlat;
use Hal\Component\Bounds\Bounds;
use Hal\Component\Evaluation\Evaluator;
use Hal\Component\File\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for run analysis
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class RunMetricsCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
                ->setName('metrics')
                ->setDescription('Run analysis')
                ->addArgument(
                    'path', InputArgument::OPTIONAL, 'Path to explore'
                )
                ->addOption(
                    'report-html',null, InputOption::VALUE_REQUIRED, 'Path to save report in HTML format. Example: /tmp/report.html'
                )
                ->addOption(
                    'report-xml', null, InputOption::VALUE_REQUIRED, 'Path to save summary report in XML format. Example: /tmp/report.xml'
                )
                ->addOption(
                    'violations-xml', null, InputOption::VALUE_REQUIRED, 'Path to save violations in XML format. Example: /tmp/report.xml'
                )
                ->addOption(
                    'report-csv', null, InputOption::VALUE_REQUIRED, 'Path to save summary report in CSV format. Example: /tmp/report.csv'
                )
                ->addOption(
                    'report-json', null, InputOption::VALUE_REQUIRED, 'Path to save detailed report in JSON format. Example: /tmp/report.json'
                )
                ->addOption(
                    'level', null, InputOption::VALUE_REQUIRED, 'Depth of summary report', 0
                )
                ->addOption(
                    'extensions', null, InputOption::VALUE_REQUIRED, 'Regex of extensions to include', null
                )
                ->addOption(
                    'excludedDirs', null, InputOption::VALUE_REQUIRED, 'Regex of subdirectories to exclude', null
                )
                ->addOption(
                    'without-oop', null, InputOption::VALUE_NONE, 'If provided, tool will not extract any information about OOP model (faster)'
                )
                ->addOption(
                    'failure-condition', null, InputOption::VALUE_REQUIRED, 'Optional failure condition, in english. For example: average.maintenabilityIndex < 50 or sum.loc > 10000', null
                )
                ->addOption(
                    'config', null, InputOption::VALUE_REQUIRED, 'Config file (YAML)', null
                )
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln('PHPMetrics by Jean-François Lépine <https://twitter.com/Halleck45>');
        $output->writeln('');

        $level = $input->getOption('level');

        // config
        $configFactory = new ConfigFactory();
        $config = $configFactory->factory($input);

        // files
        if(null === $config->getPath()->getBasePath()) {
            throw new \LogicException('Please provide a path to analyze');
        }
        $finder = new Finder(
            $config->getPath()->getExtensions()
            , $config->getPath()->getExcludedDirs()
        );

        // rules
        $rules = $config->getRuleSet();
        $validator = new \Hal\Application\Rule\Validator($rules);

        // bounds
        $bounds = new Bounds;

        // jobs queue planning
        $queue = new Queue();
        $queue
            ->push(new DoAnalyze($output, $finder, $config->getPath()->getBasePath(), !$input->getOption('without-oop')))
            ->push(new SearchBounds($output, $bounds))
            ->push(new DoAggregatedAnalyze($output, new DirectoryAggregatorFlat($level)))
            ->push(new ReportRenderer($output, new Summary\Cli($validator, $bounds)))
            ->push(new ReportWriter($config->getLogging()->getReport('html'), $output, new Summary\Html($validator, $bounds)))
            ->push(new ReportWriter($config->getLogging()->getReport('json'), $output, new Details\Json($validator, $bounds)))
            ->push(new ReportWriter($config->getLogging()->getReport('xml'), $output, new Summary\Xml($validator, $bounds)))
            ->push(new ReportWriter($config->getLogging()->getReport('csv'), $output, new Details\Csv($validator, $bounds)))
            ->push(new ReportWriter($config->getLogging()->getViolation('xml'), $output, new Xml($validator, $bounds)))
            ;

        // execute
        $collection = new \Hal\Component\Result\ResultCollection();
        $aggregatedResults = new \Hal\Component\Result\ResultCollection();
        $queue->execute($collection, $aggregatedResults);

        $output->writeln('<info>done</info>');

        // evaluation of success
        $evaluator = new Evaluator($collection, $aggregatedResults, $bounds);
        $result = $evaluator->evaluate($config->getFailureCondition());
        return $result->getCode();
    }

}
