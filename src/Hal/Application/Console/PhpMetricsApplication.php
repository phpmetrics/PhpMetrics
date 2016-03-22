<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Application\Console;

use Hal\Application\Command\InitConfigCommand;
use Hal\Application\Command\RunMetricsCommand;
use Hal\Application\Command\SelfUpdateCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Application
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class PhpMetricsApplication extends Application
{

    /**
     * Gets the name of the command based on input.
     *
     * @param InputInterface $input The input interface
     *
     * @return string The command name
     */
    protected function getCommandName(InputInterface $input)
    {
        $available = ['metrics', 'self-update', 'init'];
        $arg = $input->getFirstArgument();
        if(!in_array($arg, $available) ||'metrics' === $arg) {
            // default argument : we don't want to provide the name of the command by default
            $inputDefinition = $this->getDefinition();
            $inputDefinition->setArguments();
            $this->setDefinition($inputDefinition);
            return 'metrics';
        }
        return $arg;
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return Command[] An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();

        $defaultCommands[] = new RunMetricsCommand();
        $defaultCommands[] = new SelfUpdateCommand();
        $defaultCommands[] = new InitConfigCommand();

        return $defaultCommands;
    }
}