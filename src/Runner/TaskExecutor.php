<?php declare(strict_types=1);

namespace Phrozer\Runner;

use Phrozer\Console\CliInput;

interface TaskExecutor
{

    public function process(CliInput $input) : void;
}
