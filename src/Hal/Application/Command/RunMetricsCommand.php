<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Command;
use Hal\Component\Aggregator\DirectoryAggregator;
use Hal\Component\Bounds\Bounds;
use Hal\Component\Bounds\DirectoryBounds;
use Hal\Application\Command\Job\DoAnalyze;
use Hal\Application\Command\Job\Queue;
use Hal\Application\Command\Job\ReportRenderer;
use Hal\Application\Command\Job\ReportWriter;
use Hal\Application\Command\Job\SearchBounds;
use Hal\Component\File\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Hal\Application\Formater\Summary;
use Hal\Application\Formater\Details;
use Hal\Application\Command\Job\DoAggregatedAnalyze;

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
                        'path', InputArgument::REQUIRED, 'Path to explore'
                )
                ->addOption(
                        'summary-html',null, InputOption::VALUE_REQUIRED, 'Path to save summary report in HTML format'
                )
                ->addOption(
                        'details-html', null, InputOption::VALUE_REQUIRED, 'Path to save detailed report in HTML format'
                )
                ->addOption(
                        'summary-xml', null, InputOption::VALUE_REQUIRED, 'Path to save summary report in XML format'
                )
                ->addOption(
                        'level', null, InputOption::VALUE_REQUIRED, 'Depth of summary report', 3
                )
                ->addOption(
                        'extensions', null, InputOption::VALUE_REQUIRED, 'Regex of extensions to include', 'php'
                )
                ->addOption(
                        'without-oop', null, InputOption::VALUE_NONE, 'If provided, tool will noy extract informations about OOP model (faster)'
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

        // files
        $finder = new Finder($input->getOption('extensions'));

        // rules
        $rules = new \Hal\Application\Rule\RuleSet();
        $validator = new \Hal\Application\Rule\Validator($rules);

        // bounds
        $bounds = new Bounds;

        // jobs queue planning
        $queue = new Queue();
        $queue
            ->push(new DoAnalyze($output, $finder, $input->getArgument('path'), !$input->getOption('without-oop')))
            ->push(new SearchBounds($output, $bounds))
            ->push(new DoAggregatedAnalyze($output, new DirectoryAggregator($level)))
            ->push(new ReportRenderer($output, new Summary\Cli($validator, $bounds)))
            ->push(new ReportWriter($input->getOption('summary-html'), $output, new Summary\Html($validator, $bounds)))
            ->push(new ReportWriter($input->getOption('details-html'), $output, new Details\Html($validator)))
            ->push(new ReportWriter($input->getOption('summary-xml'), $output, new Summary\Xml($validator, $bounds)))
            ;

        // execute
        $collection = new \Hal\Component\Result\ResultCollection();
        $aggregatedResults = new \Hal\Component\Result\ResultCollection();
        $queue->execute($collection, $aggregatedResults);

        $output->writeln('<info>done</info>');

        return 0;
    }

}
