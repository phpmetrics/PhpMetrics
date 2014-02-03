<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Command;
use Hal\Command\Job\DoAnalyze;
use Hal\Command\Job\Queue;
use Hal\Command\Job\ReportRenderer;
use Hal\Command\Job\ReportWriter;
use Hal\File\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Hal\Formater\Summary;
use Hal\Formater\Details;

/**
 * Command for run analysis
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class RunMetricsCommand extends Command
{

    /**
     * @var ProgressHelper
     */
    private $progress;

    /**
     * Files to analyze
     *
     * @var array
     */
    private $files = array();

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
                        'details-html', null, InputOption::VALUE_REQUIRED, 'Path to save detailled report in HTML format'
                )
                ->addOption(
                        'level', null, InputOption::VALUE_REQUIRED, 'Depth of summary report', 3
                )
                ->addOption(
                        'extensions', null, InputOption::VALUE_REQUIRED, 'Regex of extensions to include', 'php'
                )
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $level = $input->getOption('level');

        // files
        $finder = new Finder($input->getOption('extensions'));

        // rules
        $rules = new \Hal\Rule\RuleSet();
        $validator = new \Hal\Rule\Validator($rules);

        // jobs queue planning
        $queue = new Queue();
        $queue
            ->push(new DoAnalyze($output, $finder, $input->getArgument('path')))
            ->push(new ReportRenderer($output, new Summary\Cli($validator, $output, $level)))
            ->push(new ReportWriter($input->getOption('summary-html'), $output, new Summary\Html($validator, $level)))
            ->push(new ReportWriter($input->getOption('details-html'), $output, new Details\Html($validator)))
            ;

        // execute
        $collection = new \Hal\Result\ResultCollection();
        $queue->execute($collection);

        $output->writeln('<info>done</info>');

        return 0;
    }

}
