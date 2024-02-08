<?php
declare(strict_types=1);

namespace Hal\Application;

use Hal\Component\Output\Output;
use function sprintf;
use const PHP_EOL;

/**
 * Application dedicated to only display the current version of PhpMetrics.
 */
final class VersionApplication implements ApplicationInterface
{
    public function __construct(private readonly Output $output)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function run(): int
    {
        $versionMsg = 'PhpMetrics %s <https://www.phpmetrics.org>' . PHP_EOL .
            'by Jean-François Lépine <https://twitter.com/Halleck45>' . PHP_EOL;
        $this->output->writeln(sprintf($versionMsg, VersionInfo::getVersion()));
        return 0;
    }
}
