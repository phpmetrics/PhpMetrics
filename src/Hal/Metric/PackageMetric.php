<?php

namespace Hal\Metric;

use JsonSerializable;

class PackageMetric implements Metric, JsonSerializable
{
    use BagTrait;

    public function getClasses()
    {
        return $this->has('classes') ? $this->get('classes') : [];
    }

    public function addClass($name)
    {
        $elements = $this->get('classes');
        $elements[] = (string) $name;
        $this->set('classes', $elements);
    }

    public function setAbstraction($abstraction)
    {
        $this->set('abstraction', $abstraction);
    }

    public function getAbstraction()
    {
        return $this->get('abstraction');
    }

    public function setInstability($abstraction)
    {
        $this->set('instability', $abstraction);
    }

    public function getInstability()
    {
        return $this->get('instability');
    }

    public function addOutgoingClassDependency($className, $packageName)
    {
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

    public function getOutgoingClassDependencies()
    {
        return $this->has('outgoing_class_dependencies') ? $this->get('outgoing_class_dependencies') : [];
    }

    public function getOutgoingPackageDependencies()
    {
        return $this->has('outgoing_package_dependencies') ? $this->get('outgoing_package_dependencies') : [];
    }

    public function addIncomingClassDependency($className, $packageName)
    {
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

    public function getIncomingClassDependencies()
    {
        return $this->has('incoming_class_dependencies') ? $this->get('incoming_class_dependencies') : [];
    }

    public function getIncomingPackageDependencies()
    {
        return $this->has('incoming_package_dependencies') ? $this->get('incoming_package_dependencies') : [];
    }
}
