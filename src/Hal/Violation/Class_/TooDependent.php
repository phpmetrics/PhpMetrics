<?php
declare(strict_types=1);

namespace Hal\Violation\Class_;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;

/**
 * This class triggers a violation when the efferent coupling of a class is grater than or equals to 20.
 */
final class TooDependent implements Violation
{
    private Metric $metric;

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Too dependent';
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Metric $metric): void
    {
        if (!$metric instanceof ClassMetric) {
            return;
        }

        $this->metric = $metric;

        if ($metric->get('efferentCoupling') >= 20) {
            /** @var ViolationsHandlerInterface $violationsHandler */
            $violationsHandler = $metric->get('violations');
            $violationsHandler->add($this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLevel(): int
    {
        return Violation::INFO;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        /** @var int $coupling */
        $coupling = $this->metric->get('efferentCoupling');

        return <<<EOT
This class looks use really high number of components.

* Efferent coupling is $coupling, so this class uses $coupling different external components.

Maybe you should check why this class has lot of dependencies.
EOT;
    }
}
