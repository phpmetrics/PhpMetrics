<?php declare(strict_types=1);

namespace Phrozer;

use Ahc\Cli\Application;
use Xynha\Container\DiContainer;
use Xynha\Container\DiRuleList;

final class Phrozer
{
    public const APP_NAME = 'phrozer';
    public const APP_VERSION = '0.1';

    /** @var DiContainer */
    private $dic;

    private $cli;

    public function __construct(ExitInterface $exit, string $logo)
    {
        $rules = FileLoader::loadJson(__DIR__ . '/dirules.json');

        $rlist = new DiRuleList();
        $rlist = $rlist->addRules($rules);
        $this->dic = new DiContainer($rlist);

        $this->cli = new Application(self::APP_NAME, self::APP_VERSION, [$exit, 'onExit']);
        $this->cli->logo($logo);
    }

    /** @param string[] $argv */
    public function handle(array $argv) : void
    {
        $this->cli->handle($argv);
    }
}
