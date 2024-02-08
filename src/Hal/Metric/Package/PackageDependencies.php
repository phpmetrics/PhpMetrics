<?php
declare(strict_types=1);

namespace Hal\Metric\Package;

use Hal\Metric\CalculableInterface;
use Hal\Metric\ClassMetric;
use Hal\Metric\InterfaceMetric;
use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;
use function array_map;
use function in_array;
use function strrev;
use function strstr;

/**
 * This class registers the dependencies at package level, instead of class level.
 * This metric will be used to calculate the package abstraction, package instability and package distance of the
 * analyzed project.
 *
 * This calculable metric depends on other visitors.
 * @uses PackageCollectingVisitor for the "package" metric applied on classes
 * @uses \Hal\Metric\Class_\Coupling\ExternalsVisitor for the "externals" metric applied on classes
 */
final class PackageDependencies implements CalculableInterface
{
    public function __construct(private readonly Metrics $metrics)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(): void
    {
        array_map(
            $this->increaseDependencies(...),
            [...$this->metrics->getClassMetrics(), ...$this->metrics->getInterfaceMetrics()]
        );
    }

    /**
     * Add incoming and outgoing dependencies on package level.
     *
     * @param ClassMetric|InterfaceMetric $class
     */
    private function increaseDependencies(ClassMetric|InterfaceMetric $class): void
    {
        /** @var string $packageName */
        $packageName = $class->get('package');
        /** @var PackageMetric $incomingPackage */
        $incomingPackage = $this->metrics->get($packageName);
        /** @var array<string> $externalDependencies */
        $externalDependencies = $class->get('externals');
        foreach ($externalDependencies as $outgoingClassName) {
            // Ignore dependencies that belong to the same current analyzed package.
            if (in_array($outgoingClassName, $incomingPackage->getClasses(), true)) {
                continue;
            }

            $outgoingPackageName = $this->getPackageOfClass($outgoingClassName);
            $incomingPackage->addOutgoingClassDependency($outgoingClassName, $outgoingPackageName);
            $outgoingPackage = $this->metrics->get($outgoingPackageName);

            if ($outgoingPackage instanceof PackageMetric) {
                $outgoingPackage->addIncomingClassDependency($class->getName(), $incomingPackage->getName());
            }
        }
    }

    /**
     * Get the package name from a given fully qualified class name.
     * Try to find the package name stored in the metrics. If none set, use the fully qualified class name to infer the
     * package name. When the namespace of the classname is "root" ("\"), then package name is set to "\".
     *
     * @param string $className
     * @return string
     */
    private function getPackageOfClass(string $className): string
    {
        /** @var null|string $packageName */
        $packageName = $this->metrics->get($className)?->get('package');
        if (null !== $packageName) {
            return $packageName;
        }

        // Proceed the string in reverse to try to infer the package name.
        $revPackageName = strstr(strrev($className), '\\');
        return false !== $revPackageName ? strrev($revPackageName) : '\\';
    }
}
