<?php

namespace Hal\Violation\Class_;

use Hal\Metric\ClassMetric;
use Hal\Metric\Metric;
use Hal\Violation\Violation;

class Blob implements Violation
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Blob / God object';
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

        $suspect = 0;
        if ($metric->get('nbMethodsPublic') >= 8) {
            $suspect++;
        }

        if ($metric->get('lcom') >= 3) {
            $suspect++;
        }

        if (count($metric->get('externals')) >= 8) {
            $suspect++;
        }

        if ($suspect >= 3) {
            $metric->get('violations')->add($this);
        }
    }

    /**
     * @inheritdoc
     */
    public function getLevel()
    {
        return Violation::ERROR;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return <<<EOT
            A blob object (or "god class") does not follow the Single responsibility principle.

            * object has lot of public methods  ({$this->metric->get('nbMethodsPublic')}, excluding getters and setters)
            * object has a high Lack of cohesion of methods (LCOM={$this->metric->get('lcom')})
            * object knows everything (and use lot of external classes)

            Maybe you should reducing the number of methods splitting this object in many sub objects.
EOT;
    }
}
