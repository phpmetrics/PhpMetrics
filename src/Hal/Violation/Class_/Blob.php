<?php
declare(strict_types=1);

namespace Hal\Violation\Class_;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Violation;
use Hal\Violation\ViolationsHandlerInterface;

/**
 * This class triggers a violation when the class is detected as a Blob or a God object.
 * This detection possible by looking at the number of public methods, the LCOM and the number of dependencies to other
 * classes.
 */
final class Blob implements Violation
{
    private Metric $metric;

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'Blob / God object';
    }

    /**
     * {@inheritDoc}
     */
    public function apply(Metric $metric): void
    {
        if (!$metric instanceof ClassMetric) {
            return;
        }
        $this->metric = $metric;

        if (
            ($metric->get('nbMethodsPublic') >= 8) &&
            ($metric->get('lcom') >= 3) &&
            (count($metric->get('externals')) >= 8)
        ) {
            /** @var ViolationsHandlerInterface $violationsHandler */
            $violationsHandler = $metric->get('violations');
            $violationsHandler->add($this);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getLevel(): int
    {
        return Violation::ERROR;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return <<<EOT
A blob object (or "god class") does not follow the Single responsibility principle.

* object has lot of public methods ({$this->metric->get('nbMethodsPublic')}, excluding getters and setters)
* object has a high Lack of cohesion of methods (LCOM={$this->metric->get('lcom')})
* object knows everything (and use lot of external classes)

Maybe you should reducing the number of methods splitting this object in many sub objects.
EOT;
    }
}
