<?php declare(strict_types=1);

namespace Phpmetrix\Runner;

use Phpmetrix\Console\CliInput;

interface TaskExecutor
{

    public function process(CliInput $input) : void;
}
