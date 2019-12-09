<?php
namespace Hal\Violation\Class_;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Violation;

class TooDependent implements Violation
{

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Too dependent';
    }

    /**
     * @inheritdoc
     */
    public function apply(Metric $metric)
    {
        if (!$metric instanceof ClassMetric) {
            return;
        }

        $this->metric = $metric;

        if ($metric->get('efferentCoupling') >= 20) {
            $metric->get('violations')->add($this);
            return;
        }
    }

    /**
     * @inheritdoc
     */
    public function getLevel()
    {
        return Violation::INFO;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return <<<EOT
This class looks use really high number of components.

* Efferent coupling is {$this->metric->get('efferentCoupling')}, so this class uses {$this->metric->get('efferentCoupling')} different external components.

Maybe you should check why this class has lot of dependencies.
EOT;
    }
}
