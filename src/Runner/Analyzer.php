<?php declare(strict_types=1);

namespace Phpmetrix\Runner;

use Phpmetrix\Console\CliInput;
use Phpmetrix\Parser\PhpParser;
use RuntimeException;
use Symfony\Component\Finder\Finder;

final class Analyzer implements TaskExecutor
{

    private $parser;

    public function __construct(PhpParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @throws \RuntimeException
     * @throws \Phpmetrix\Parser\ParserException
     */
    public function process(CliInput $input) : void
    {
        $files = new Finder();
        $files->in($input->directories());
        $files->notPath($input->excludePaths());
        $files->name($input->filenames());

        foreach ($files->files() as $item) {
            $this->parser->parse($item);
        }
    }
}
