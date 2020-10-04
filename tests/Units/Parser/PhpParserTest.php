<?php declare(strict_types=1);

use Phpmetrix\Parser\PhpParser;
use PhpParser\Error;
use PhpParser\NodeTraverserInterface;
use PhpParser\ParserFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @covers Phpmetrix\Parser\PhpParser
 */
final class PhpParserTest extends TestCase
{

    /** @var PhpParser */
    private $parser;

    /** @var MockObject */
    private $traverser;

    /** @return void */
    protected function setUp()
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->traverser = $this->createMock(NodeTraverserInterface::class);

        $this->parser = new PhpParser($parser, $this->traverser);
    }

    public function testParseUnknownFile()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('#failed to open stream#');

        $spl = new SplFileInfo(__DIR__ . '/_data/unkown.php', '', '');
        $this->parser->parse($spl);
    }

    public function testParseSyntaxError()
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessageRegExp('#Syntax error#');

        $spl = new SplFileInfo(__DIR__ . '/_data/syntax-error.inc', '', '');
        $this->parser->parse($spl);
    }

    public function testParseShouldReturnNull()
    {
        $this->traverser->expects($this->once())->method('traverse');

        $spl = new SplFileInfo(__DIR__ . '/_data/php74.inc', '', '');
        $this->parser->parse($spl);
    }
}
