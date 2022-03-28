<?php
declare(strict_types=1);

namespace Hal\Application\Workflow\Task;

use Error;
use Hal\Component\Output\Output;
use PhpParser\NodeTraverserInterface;
use PhpParser\Parser;
use function file_get_contents;
use function sprintf;

/**
 * This class is in charge of the task about parsing the content of each given file to build an Abstract Syntax Tree for
 * each of them, and traverse all created nodes linked by the AST results.
 */
final class PrepareParserTask implements WorkflowTaskInterface
{
    public function __construct(
        private readonly Parser $parser,
        private readonly NodeTraverserInterface $nodeTraverser,
        private readonly Output $output
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function process(array $files): void
    {
        foreach ($files as $file) {
            try {
                $this->nodeTraverser->traverse($this->parser->parse(file_get_contents($file)));
            } catch (Error) {
                $this->output->writeln(sprintf('<error>Cannot parse %s</error>', $file));
            }
        }
    }
}
