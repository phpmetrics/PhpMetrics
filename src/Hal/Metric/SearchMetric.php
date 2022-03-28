<?php
declare(strict_types=1);

namespace Hal\Metric;

use JsonSerializable;

/**
 * Contains all metrics related to research of violations.
 */
class SearchMetric implements Metric, JsonSerializable
{
    use BagTrait;
}
