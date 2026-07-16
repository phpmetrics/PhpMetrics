<?php
namespace Test\Hal\Metric\System\Packages\Composer;

use Hal\Application\Config\Config;
use Hal\Metric\System\Packages\Composer\Composer;

/**
 * Exposes the protected discovery methods so the file resolution logic can be
 * tested without hitting the network (Packagist) through calculate().
 */
class ComposerTestable extends Composer
{
    public function jsonRequirements()
    {
        return $this->getComposerJsonRequirements();
    }

    public function lockInstalled(array $rootPackageRequirements)
    {
        return $this->getComposerLockInstalled($rootPackageRequirements);
    }
}

/**
 * @group metric
 * @group composer
 */
class ComposerTest extends \PHPUnit\Framework\TestCase
{
    private function fixture($relative = '')
    {
        return __DIR__ . '/fixtures/project' . $relative;
    }

    private function build(array $configValues)
    {
        $config = new Config();
        $config->fromArray($configValues);

        return new ComposerTestable($config, []);
    }

    /**
     * Historical behaviour: when the analyzed directory contains the composer.json,
     * it is discovered as before (composer enabled, no explicit path).
     */
    public function testItDiscoversComposerJsonInsideAnalyzedDirectory(): void
    {
        $composer = $this->build(['files' => [$this->fixture()]]);

        $requirements = $composer->jsonRequirements();

        $this->assertArrayHasKey('monolog/monolog', $requirements);
        $this->assertArrayHasKey('acme/private', $requirements);
    }

    /**
     * Backward-compatible: analyzing only ./app (which has no composer.json) finds
     * no requirements when no explicit composer path is provided.
     */
    public function testItFindsNoRequirementsWhenComposerJsonIsOutsideAnalyzedDirectory(): void
    {
        $composer = $this->build(['files' => [$this->fixture('/app')]]);

        $this->assertSame([], $composer->jsonRequirements());
    }

    /**
     * New behaviour: --composer=<path> decouples composer.json discovery from the
     * analyzed directories, pointing to a file.
     */
    public function testComposerPathOptionResolvesRequirementsFromFile(): void
    {
        $composer = $this->build([
            'files' => [$this->fixture('/app')],
            'composer' => $this->fixture('/composer.json'),
        ]);

        $requirements = $composer->jsonRequirements();

        $this->assertArrayHasKey('monolog/monolog', $requirements);
        $this->assertArrayHasKey('acme/private', $requirements);
    }

    /**
     * --composer=<path> may also point to the directory holding composer.json.
     */
    public function testComposerPathOptionResolvesRequirementsFromDirectory(): void
    {
        $composer = $this->build([
            'files' => [$this->fixture('/app')],
            'composer' => $this->fixture(),
        ]);

        $this->assertArrayHasKey('monolog/monolog', $composer->jsonRequirements());
    }

    /**
     * The composer.lock sibling of the given composer path is used for the
     * installed versions.
     */
    public function testComposerPathOptionResolvesInstalledFromSiblingLock(): void
    {
        $composer = $this->build([
            'files' => [$this->fixture('/app')],
            'composer' => $this->fixture('/composer.json'),
        ]);

        $installed = $composer->lockInstalled(['monolog/monolog']);

        $this->assertSame(['monolog/monolog' => '1.25.3'], $installed);
    }

    /**
     * An unresolvable composer path yields no files instead of raising an error.
     */
    public function testComposerPathOptionWithMissingFileReturnsEmpty(): void
    {
        $composer = $this->build([
            'files' => [$this->fixture('/app')],
            'composer' => $this->fixture('/does-not-exist/composer.json'),
        ]);

        $this->assertSame([], $composer->jsonRequirements());
        $this->assertSame([], $composer->lockInstalled(['monolog/monolog']));
    }
}
