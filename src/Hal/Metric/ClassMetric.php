<?php
declare(strict_types=1);

namespace Hal\Metric;

use JsonSerializable;

/**
 * Contains all metrics related to a class.
 */
class ClassMetric implements Metric, JsonSerializable
{
    use BagTrait;
}
