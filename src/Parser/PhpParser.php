<?php declare(strict_types=1);

namespace Phpmetrix\Parser;

use PhpParser\Error;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use RuntimeException;
use Symfony\Component\Finder\SplFileInfo;

final class PhpParser
{

    private $parser;

    private $traverser;

    public function __construct(Parser $parser, NodeTraverserInterface $traverser)
    {
        $this->parser = $parser;
        $this->traverser = $traverser;
    }

    public function addVisitor(NodeVisitor $visitor) : void
    {
        $this->traverser->addVisitor($visitor);
    }

    /**
     * @throws \RuntimeException Throws when can't get file content or parse error
     * @throws \Phpmetrix\Parser\ParserException Throws when parse function return null
     */
    public function parse(SplFileInfo $file) : void
    {
        try {
            $stmt = $this->parser->parse($file->getContents());
        } catch (Error $exc) {
            throw new ParserException($file->getRelativePathname() . ': ' . $exc->getMessage());
        }

        if ($stmt === null) {
            throw new ParserException('Parse function return null when parsing ' . $file->getRelativePathname());
        }

        $this->traverser->traverse($stmt);
    }
}
