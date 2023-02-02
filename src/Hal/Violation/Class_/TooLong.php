<?php
declare(strict_types=1);

namespace Hal\Violation\Class_;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;

/**
 * This class triggers a violation when the number of logical lines of code into a single class is over 200.
 */
final class TooLong implements Violation
{
    private Metric $metric;

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Too long';
    }

    /**
     * @inheritdoc
     */
    public function apply(Metric $metric): void
    {
        if (!$metric instanceof ClassMetric) {
            return;
        }

        $this->metric = $metric;

        if ($metric->get('lloc') >= 200) {
            /** @var ViolationsHandlerInterface $violationsHandler */
            $violationsHandler = $metric->get('violations');
            $violationsHandler->add($this);
        }
    }

    /**
     * @inheritdoc
     */
    public function getLevel(): int
    {
        return Violation::INFO;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        /** @var int $lloc */
        $lloc = $this->metric->get('lloc');
        /** @var int $loc */
        $loc = $this->metric->get('loc');

        return <<<EOT
This class looks really long.

* Class has $lloc logical lines of code
* Class has $loc lines of code

Maybe your class should not exceed 200 lines of logical code
EOT;
    }
}
