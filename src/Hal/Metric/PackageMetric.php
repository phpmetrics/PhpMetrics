<?php

namespace Hal\Metric;

use JsonSerializable;

class PackageMetric implements Metric, JsonSerializable
{
    use BagTrait;

    /** @return string[] */
    public function getClasses()
    {
        return $this->has('classes') ? $this->get('classes') : [];
    }

    /** @param string $name */
    public function addClass($name)
    {
        $elements = $this->get('classes');
        $elements[] = (string) $name;
        $this->set('classes', $elements);
    }

    /** @param float $abstraction */
    public function setAbstraction($abstraction)
    {
        if ($abstraction !== null) {
            $abstraction = (float) $abstraction;
        }
        $this->set('abstraction', $abstraction);
    }

    /** @return float|null */
    public function getAbstraction()
    {
        return $this->get('abstraction');
    }

    /** @param float $instability */
    public function setInstability($instability)
    {
        if ($instability !== null) {
            $instability = (float) $instability;
        }
        $this->set('instability', $instability);
    }

    /** @return float|null */
    public function getInstability()
    {
        return $this->get('instability');
    }

    /**
     * @param string $className
     * @param string $packageName
     */
    public function addOutgoingClassDependency($className, $packageName)
    {
        if ($packageName === $this->getName()) {
            return;
        }
        $classDependencies = $this->getOutgoingClassDependencies();
        $packageDependencies = $this->getOutgoingPackageDependencies();
        if (! in_array($className, $classDependencies)) {
            $classDependencies[] = $className;
            $this->set('outgoing_class_dependencies', $classDependencies);
        }
        if (! in_array($packageName, $packageDependencies)) {
            $packageDependencies[] = $packageName;
            $this->set('outgoing_package_dependencies', $packageDependencies);
        }
    }

    /** @return string[] */
    public function getOutgoingClassDependencies()
    {
        return $this->has('outgoing_class_dependencies') ? $this->get('outgoing_class_dependencies') : [];
    }

    /** @return string[] */
    public function getOutgoingPackageDependencies()
    {
        return $this->has('outgoing_package_dependencies') ? $this->get('outgoing_package_dependencies') : [];
    }

    /**
     * @param string $className
     * @param string $packageName
     */
    public function addIncomingClassDependency($className, $packageName)
    {
        if ($packageName === $this->getName()) {
            return;
        }
        $classDependencies = $this->getIncomingClassDependencies();
        $packageDependencies = $this->getIncomingPackageDependencies();
        if (! in_array($className, $classDependencies)) {
            $classDependencies[] = $className;
            $this->set('incoming_class_dependencies', $classDependencies);
        }
        if (! in_array($packageName, $packageDependencies)) {
            $packageDependencies[] = $packageName;
            $this->set('incoming_package_dependencies', $packageDependencies);
        }
    }

    /** @return string[] */
    public function getIncomingClassDependencies()
    {
        return $this->has('incoming_class_dependencies') ? $this->get('incoming_class_dependencies') : [];
    }

    /** @return string[] */
    public function getIncomingPackageDependencies()
    {
        return $this->has('incoming_package_dependencies') ? $this->get('incoming_package_dependencies') : [];
    }

    /** @param float $normalizedDistance */
    public function setNormalizedDistance($normalizedDistance)
    {
        $this->set('distance', $normalizedDistance / sqrt(2.0));
        $this->set('normalized_distance', $normalizedDistance);
    }

    /** @return float|null */
    public function getDistance()
    {
        return $this->get('distance');
    }

    /** @return float|null */
    public function getNormalizedDistance()
    {
        return $this->get('normalized_distance');
    }

    /** @param float[] $instabilities */
    public function setDependentInstabilities(array $instabilities)
    {
        $this->set('dependent_instabilities', $instabilities);
    }

    /** @return float[] */
    public function getDependentInstabilities()
    {
        return $this->has('dependent_instabilities') ? $this->get('dependent_instabilities') : [];
    }
}
