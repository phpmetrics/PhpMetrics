<?php
declare(strict_types=1);

namespace Hal\Metric;

use JsonSerializable;

/**
 * Contains all metrics related to a function (out of a class: we are not talking about methods here).
 */
class FunctionMetric implements Metric, JsonSerializable
{
    use BagTrait;
}
