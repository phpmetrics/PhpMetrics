<?php declare(strict_types=1);

namespace Phpmetrix;

use Ahc\Cli\Application;
use Phpmetrix\Console\Command\AnalyzeCommand;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class CliApplication
{
    public const APP_NAME = 'Phpmetrix';
    public const APP_VERSION = '0.1';

    private $dic;

    private $cli;

    public function __construct(ContainerInterface $dic, ExitInterface $exit)
    {
        $this->dic = $dic;
        $this->cli = new Application(self::APP_NAME, self::APP_VERSION, [$exit, 'onExit']);
    }

    /**
     * @param string[] $argv
     *
     * @throws ContainerExceptionInterface
     */
    public function handle(array $argv) : void
    {
        $this->cli->add($this->dic->get(AnalyzeCommand::class));
        $this->cli->handle($argv);
    }
}
