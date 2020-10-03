<?php declare(strict_types=1);

namespace Phrozer\Component;

use Phrozer\Console\CliInput;

interface TaskExecutor
{

    public function process(CliInput $input) : void;
}
