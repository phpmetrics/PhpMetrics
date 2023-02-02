<?php
declare(strict_types=1);

namespace Hal\Metric;

use JsonSerializable;
use function in_array;
use function sqrt;

/**
 * Contains all metrics related to a whole package.
 */
class PackageMetric implements Metric, JsonSerializable
{
    use BagTrait;

    /**
     * @return array<int, string>
     */
    public function getClasses(): array
    {
        /** @var array<int, string> */
        return $this->get('classes') ?? [];
    }

    /**
     * @param string $name
     */
    public function addClass(string $name): void
    {
        $this->set('classes', [...$this->getClasses(), $name]);
    }

    /**
     * @param float $abstraction
     */
    public function setAbstraction(float $abstraction): void
    {
        $this->set('abstraction', $abstraction);
    }

    /**
     * @return float|null
     */
    public function getAbstraction(): null|float
    {
        /** @var null|float */
        return $this->get('abstraction');
    }

    /**
     * @param float $instability
     */
    public function setInstability(float $instability): void
    {
        $this->set('instability', $instability);
    }

    /**
     * @return float|null
     */
    public function getInstability(): null|float
    {
        /** @var null|float */
        return $this->get('instability');
    }

    /**
     * @param string $className
     * @param string $packageName
     */
    public function addOutgoingClassDependency(string $className, string $packageName): void
    {
        if ($packageName === $this->getName()) {
            return;
        }
        $classDependencies = $this->getOutgoingClassDependencies();
        $packageDependencies = $this->getOutgoingPackageDependencies();
        if (!in_array($className, $classDependencies, true)) {
            $this->set('outgoing_class_dependencies', [...$classDependencies, $className]);
        }
        if (!in_array($packageName, $packageDependencies, true)) {
            $this->set('outgoing_package_dependencies', [...$packageDependencies, $packageName]);
        }
    }

    /**
     * @return array<string>
     */
    public function getOutgoingClassDependencies(): array
    {
        /** @var array<string> */
        return $this->get('outgoing_class_dependencies') ?? [];
    }

    /**
     * @return array<string>
     */
    public function getOutgoingPackageDependencies(): array
    {
        /** @var array<string> */
        return $this->get('outgoing_package_dependencies') ?? [];
    }

    /**
     * @param string $className
     * @param string $packageName
     */
    public function addIncomingClassDependency(string $className, string $packageName): void
    {
        if ($packageName === $this->getName()) {
            return;
        }
        $classDependencies = $this->getIncomingClassDependencies();
        $packageDependencies = $this->getIncomingPackageDependencies();
        if (!in_array($className, $classDependencies, true)) {
            $this->set('incoming_class_dependencies', [...$classDependencies, $className]);
        }
        if (!in_array($packageName, $packageDependencies, true)) {
            $this->set('incoming_package_dependencies', [...$packageDependencies, $packageName]);
        }
    }

    /**
     * @return array<string>
     */
    public function getIncomingClassDependencies(): array
    {
        /** @var array<string> */
        return $this->get('incoming_class_dependencies') ?? [];
    }

    /**
     * @return array<string>
     */
    public function getIncomingPackageDependencies(): array
    {
        /** @var array<string> */
        return $this->get('incoming_package_dependencies') ?? [];
    }

    /**
     * @param float $normalizedDistance
     */
    public function setNormalizedDistance(float $normalizedDistance): void
    {
        $this->set('distance', $normalizedDistance / sqrt(2));
        $this->set('normalized_distance', $normalizedDistance);
    }

    /**
     * @return float|null
     */
    public function getDistance(): null|float
    {
        /** @var null|float */
        return $this->get('distance');
    }

    /**
     * @return float|null
     */
    public function getNormalizedDistance(): null|float
    {
        /** @var null|float */
        return $this->get('normalized_distance');
    }

    /**
     * @param array<float> $instabilities
     */
    public function setDependentInstabilities(array $instabilities): void
    {
        $this->set('dependent_instabilities', $instabilities);
    }

    /**
     * @return array<float>
     */
    public function getDependentInstabilities(): array
    {
        /** @var array<float> */
        return $this->get('dependent_instabilities') ?? [];
    }
}
