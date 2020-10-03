<?php declare(strict_types=1);

namespace Phrozer;

use Ahc\Cli\Application;
use Phrozer\Console\Command\AnalyseCommand;
use Psr\Container\ContainerInterface;
use Xynha\Container\DiContainer;

final class Phrozer
{
    public const APP_NAME = 'phrozer';
    public const APP_VERSION = '0.1';

    /** @var DiContainer */
    private $dic;

    private $cli;

    public function __construct(ContainerInterface $dic, ExitInterface $exit, string $logo)
    {
        $this->dic = $dic;

        $this->cli = new Application(self::APP_NAME, self::APP_VERSION, [$exit, 'onExit']);
        $this->cli->logo($logo);
    }

    /** @param string[] $argv */
    public function handle(array $argv) : void
    {
        $this->cli->add($this->dic->get(AnalyseCommand::class));

        $this->cli->handle($argv);
    }
}
