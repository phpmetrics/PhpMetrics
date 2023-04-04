<?php
declare(strict_types=1);

namespace Hal\Application\Workflow\Task;

use Error;
use Hal\Component\File\ReaderInterface;
use Hal\Component\Output\Output;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverserInterface;
use PhpParser\Parser;
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
        private readonly Output $output,
        private readonly ReaderInterface $fileReader
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function process(array $files): void
    {
        foreach ($files as $file) {
            /** @var string $content File exists as this have been tested in the validation earlier. */
            $content = $this->fileReader->read($file);
            try {
                /** @var array<Stmt> $nodes Can not be NULL as an exception is thrown if any error. */
                $nodes = $this->parser->parse($content);
                $this->nodeTraverser->traverse($nodes);
            } catch (Error) {
                $this->output->writeln(sprintf('<error>Cannot parse %s</error>', $file));
            }
        }
    }
}
