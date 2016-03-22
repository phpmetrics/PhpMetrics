<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Command;
use Hal\Application\Command\Job\QueueFactory;
use Hal\Application\Config\ConfigDumper;
use Hal\Application\Config\ConfigFactory;
use Hal\Application\Rule\DefaultRuleSet;
use Hal\Component\Bounds\Bounds;
use Hal\Component\Evaluation\Evaluator;
use Hal\Component\File\Finder;
use Hal\Component\Phar\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for updating phar
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class InitConfigCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
                ->setName('init')
                ->setDescription('Create a .phpmetrics.yml config file')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ruleset = new DefaultRuleSet();
        $destination = '.phpmetrics.yml';
        $dumper = new ConfigDumper($destination, $ruleset);
        $dumper->dump();

        $output->writeln('<info>Done</info>');
        return 0;
    }

}
