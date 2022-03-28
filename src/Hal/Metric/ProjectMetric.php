<?php
declare(strict_types=1);

namespace Hal\Metric;

use JsonSerializable;

/**
 * Contains all metrics related to a whole project.
 */
class ProjectMetric implements Metric, JsonSerializable
{
    use BagTrait;
}
