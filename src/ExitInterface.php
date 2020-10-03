<?php declare(strict_types=1);

namespace Phrozer;

interface ExitInterface
{

    public function onExit(int $exitCode = 0)  : void;
}
