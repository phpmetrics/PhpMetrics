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
class SelfUpdateCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
                ->setName('self-update')
                ->addArgument(
                    'version', InputArgument::OPTIONAL, 'Required version. Ex: "v1.6.2" (default: "latest")', 'latest'
                )
                ->setDescription('Updates phar archive')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>Installing %s version...</info>', $input->getArgument('version')));

        $updater = new Updater($output);
        $version = $updater->updates($input->getArgument('version'));

        $output->writeln('');
        $output->writeln(sprintf('<info>Done. PhpMetrics updated to %s</info>', $version));
        return 0;
    }

}
