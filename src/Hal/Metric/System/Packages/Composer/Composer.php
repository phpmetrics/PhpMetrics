<?php
declare(strict_types=1);

namespace Hal\Metric\System\Packages\Composer;

use Hal\Component\File\FinderInterface;
use Hal\Metric\CalculableInterface;
use Hal\Metric\Metrics;
use Hal\Metric\ProjectMetric;
use JsonException;
use function array_column;
use function array_filter;
use function array_flip;
use function array_intersect_key;
use function array_keys;
use function array_map;
use function array_merge;
use function array_walk;
use function file_get_contents;
use function json_decode;
use function preg_replace;
use function str_contains;
use function str_starts_with;
use function strtolower;
use function version_compare;
use const ARRAY_FILTER_USE_KEY;
use const JSON_THROW_ON_ERROR;

/**
 * This class computes information from Composer dependencies used in the analysed project.
 */
final class Composer implements CalculableInterface
{
    /**
     * @param Metrics $metrics
     * @param null|bool $isComposerEnabled If null, default value is true. Only disable this calculation when false.
     * @param array<string> $pathsList
     * @param FinderInterface $composerJsonFinder
     * @param FinderInterface $composerLockFinder
     * @param ComposerRegistryConnectorInterface $composerRegistryConnector
     */
    public function __construct(
        private readonly Metrics $metrics,
        private readonly null|bool $isComposerEnabled,
        private readonly array $pathsList,
        private readonly FinderInterface $composerJsonFinder,
        private readonly FinderInterface $composerLockFinder,
        private readonly ComposerRegistryConnectorInterface $composerRegistryConnector,
    ) {
    }

    /**
     * {@inheritDoc}
     * @throws JsonException
     */
    public function calculate(): void
    {
        if (false === $this->isComposerEnabled) {
            return;
        }

        $projectMetric = new ProjectMetric('composer');
        $this->metrics->attach($projectMetric);

        $rawRequirements = $this->getComposerJsonRequirements();
        // Exclude PHP itself and extensions.
        $rawRequirements = array_filter($rawRequirements, static function (string $package): bool {
            return 'php' !== strtolower($package) && !str_starts_with($package, 'ext-');
        }, ARRAY_FILTER_USE_KEY);
        $rawInstalled = $this->getComposerLockInstalled(array_keys($rawRequirements));

        $packages = [];
        foreach ($rawRequirements as $requirement => $version) {
            // TODO: $package shouldn't be stdClass but some kind of ComposerPackage entity.
            $package = $this->composerRegistryConnector->get($requirement);

            $package->installed = $rawInstalled[$requirement] ?? null;
            $package->required = $version;
            $package->name = $requirement;
            // Manage case where the package is not hosted on packagist (private repository) so we can't know the status
            if (null === $package->installed || null === $package->latest) {
                $package->status = 'unknown';
            } else {
                $package->status = version_compare($package->installed, $package->latest, '<') ? 'outdated' : 'latest';
            }
            $packages[$requirement] = $package;
        }

        $projectMetric->set('packages', $packages);
        $projectMetric->set('packages-installed', $rawInstalled);
    }

    /**
     * Returns the requirements defined in the "composer(-dist)?.json" file.
     *
     * @return array<string, string>
     * @throws JsonException When the "composer.json" file cannot be decoded from JSON.
     */
    private function getComposerJsonRequirements(): array
    {
        $rawRequirements = array_map(static function (string $filename): array {
            if (!str_contains($filename, 'composer.json') && !str_contains($filename, 'composer-dist.json')) {
                return [];
            }
            /**
             * @noinspection JsonEncodingApiUsageInspection TODO: Wait for a fix
             * @see https://github.com/kalessil/phpinspectionsea/issues/1725
             */
            $composerJson = json_decode(file_get_contents($filename), true, flags: JSON_THROW_ON_ERROR);
            $composerJson += ['require' => []];
            return $composerJson['require'];
        }, $this->composerJsonFinder->fetch([...$this->pathsList, './']));

        return array_merge([], ...$rawRequirements);
    }

    /**
     * Returns the installed packages from the "composer.lock" file.
     *
     * @param array<string> $rootPackageRequired List of root required packages to only match installed packages.
     * @return array<string, string>
     * @throws JsonException When the "composer.lock" file cannot be decoded from JSON.
     */
    private function getComposerLockInstalled(array $rootPackageRequired): array
    {
        $rawInstalled = array_map(static function (string $filename) use ($rootPackageRequired): array {
            if (!str_contains($filename, 'composer.lock')) {
                return [];
            }
            /**
             * @noinspection JsonEncodingApiUsageInspection TODO: Wait for a fix
             * @see https://github.com/kalessil/phpinspectionsea/issues/1725
             */
            $composerLock = json_decode(file_get_contents($filename), true, flags: JSON_THROW_ON_ERROR);
            $composerLock += ['packages' => []];

            // List all installed packages versions by name.
            // Only keep packages under the root required packages (not keeping dependencies of dependencies).
            // Then normalize the version format.
            $installed = array_column($composerLock['packages'], 'version', 'name');
            $installed = array_intersect_key($installed, array_flip($rootPackageRequired));
            array_walk($installed, static function (string &$packageVersion): void {
                $packageVersion = preg_replace('#[^.\d]#', '', $packageVersion);
            });
            return $installed;
        }, $this->composerLockFinder->fetch([...$this->pathsList, './']));

        return array_merge([], ...$rawInstalled);
    }
}
