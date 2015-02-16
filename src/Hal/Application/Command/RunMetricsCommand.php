<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Command;
use Hal\Application\Command\Job\QueueFactory;
use Hal\Application\Config\ConfigFactory;
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
                    'report-cli', null, InputOption::VALUE_NONE, 'Enable report in terminal'
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
                    'chart-bubbles', null, InputOption::VALUE_REQUIRED, 'Path to save Bubbles chart, in SVG format. Example: /tmp/chart.svg. Graphviz **IS** required'
                )
                ->addOption(
                    'level', null, InputOption::VALUE_REQUIRED, 'Depth of summary report', 0
                )
                ->addOption(
                    'extensions', null, InputOption::VALUE_REQUIRED, 'Regex of extensions to include', null
                )
                ->addOption(
                    'excluded-dirs', null, InputOption::VALUE_REQUIRED, 'Regex of subdirectories to exclude', null
                )
                ->addOption(
                    'symlinks', null, InputOption::VALUE_NONE, 'Enable following symlinks'
                )
                ->addOption(
                    'without-oop', null, InputOption::VALUE_NONE, 'If provided, tool will not extract any information about OOP model (faster)'
                )
                ->addOption(
                    'failure-condition', null, InputOption::VALUE_REQUIRED, 'Optional failure condition, in english. For example: average.maintainabilityIndex < 50 or sum.loc > 10000', null
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

        // config
        $configFactory = new ConfigFactory();
        $config = $configFactory->factory($input);

        // files
        if(null === $config->getPath()->getBasePath()) {
            throw new \LogicException('Please provide a path to analyze');
        }

        // files to analyze
        $finder = new Finder(
            $config->getPath()->getExtensions()
            , $config->getPath()->getExcludedDirs()
            , $config->getPath()->isFollowSymlinks() ? Finder::FOLLOW_SYMLINKS : null
        );

        // prepare structures
        $bounds = new Bounds();
        $collection = new \Hal\Component\Result\ResultCollection();
        $aggregatedResults = new \Hal\Component\Result\ResultCollection();

        // execute analyze
        $queueFactory = new QueueFactory($input, $output, $config);
        $queue = $queueFactory->factory($finder, $bounds);
        gc_disable();
        $queue->execute($collection, $aggregatedResults);
        gc_enable();

        $output->writeln('<info>done</info>');

        // evaluation of success
        $evaluator = new Evaluator($collection, $aggregatedResults, $bounds);
        $result = $evaluator->evaluate($config->getFailureCondition());
        return $result->getCode();
    }

}
