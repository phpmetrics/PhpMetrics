<?php declare(strict_types=1);

namespace Hal\Metric;

use Exception;

final class MetricNullException extends Exception
{
    public function __construct(string $name, string $classname)
    {
        $msg = sprintf('Metric "%s" is null, called in %s', $name, $classname);
        parent::__construct($msg);
    }
}
