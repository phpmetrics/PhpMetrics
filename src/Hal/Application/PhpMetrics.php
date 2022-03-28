<?php
declare(strict_types=1);

namespace Hal\Application;

use Exception;
use Hal\Component\Output\Output;
use Hal\Violation\Checkers\ViolationsCheckerInterface;
use function ini_set;
use const PHP_EOL;

/**
 * Main application executed when the configuration is correct.
 */
final class PhpMetrics implements ApplicationInterface
{
    public function __construct(
        private readonly AnalyzerInterface $analyzer,
        private readonly ReporterHandlerInterface $reporterHandler,
        private readonly ViolationsCheckerInterface $violationsChecker,
        private readonly Output $output
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function run(): int
    {
        // config
        ini_set('xdebug.max_nesting_level', '3000');
        try {
            $this->reporterHandler->report($this->analyzer->process());
            $this->violationsChecker->check();
        } catch (Exception $e) {
            $this->output->writeln(PHP_EOL . '<error>' . $e->getMessage() . '</error>' . PHP_EOL);
            return 1;
        }
        $this->output->writeln(PHP_EOL . '<success>Done</success>' . PHP_EOL);
        return 0;
    }
}
