<?php
declare(strict_types=1);

namespace Hal\Application;

use Hal\Application\Config\ConfigBagInterface;
use Hal\Component\Output\Output;
use function sprintf;

/**
 * Application dedicated to when the configuration is in error.
 */
final class ErrorApplication implements ApplicationInterface
{
    public function __construct(
        private readonly ConfigBagInterface $config,
        private readonly Output $output
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function run(): int
    {
        /** @var string $error */
        $error = $this->config->get('config-error');
        $this->output->writeln(sprintf("\n<error>%s</error>\n", $error));
        return 1;
    }
}
