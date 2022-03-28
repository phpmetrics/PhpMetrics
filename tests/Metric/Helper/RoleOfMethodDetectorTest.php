<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\Helper;

use Generator;
use Hal\Metric\Helper\RoleOfMethodDetector;
use Hal\Metric\Helper\SimpleNodeIterator;
use PhpParser\Node\Stmt\Class_;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use function array_map;

final class RoleOfMethodDetectorTest extends TestCase
{
    /**
     * @return Generator<string, array{0: null|string, 1: string}>
     */
    public function provideExamples(): Generator
    {
        $code = '<?php class A { function _(){ return $this->x; } }';
        yield 'Getter' => ['getter', $code];

        $code = '<?php class A { function _(){ return (string)$this->x; } }';
        yield 'Getter with cast' => ['getter', $code];

        $code = '<?php class A { function _(): string { return $this->x; } }';
        yield 'Getter with return scalar' => ['getter', $code];

        $code = '<?php class A { function _(): Name { return $this->x; } }';
        yield 'Getter with return object' => ['getter', $code];

        $code = '<?php class A { function _(): ?bool { return $this->x; } }';
        yield 'Getter with return optional' => ['getter', $code];

        $code = '<?php class A { function _(): string|int { return $this->x; } }';
        yield 'Getter with return UnionType' => ['getter', $code];

        $code = '<?php class A { function _(): NameInterface&Name { return $this->x; } }';
        yield 'Getter with return IntersectType' => ['getter', $code];

        $code = '<?php class A { function _($x){ $this->x = $x; } }';
        yield 'Setter' => ['setter', $code];

        $code = '<?php class A { function _($x){ $this->x = (string)$x; } }';
        yield 'Setter with cast' => ['setter', $code];

        $code = '<?php class A { function _($x){ $this->x = $x; return $this; } }';
        yield 'Fluent setter' => ['setter', $code];

        $code = '<?php class A { function _(string $x): void { $this->x = $x; } }';
        yield 'Setter with scalar hint and return void' => ['setter', $code];

        $code = '<?php class A { function _(Name $x): void { $this->x = $x; } }';
        yield 'Setter with object hint and return void' => ['setter', $code];

        $code = '<?php class A { function _(?bool $x): void { $this->x = $x; } }';
        yield 'Setter with optional hint and return void' => ['setter', $code];

        $code = '<?php class A { function _(string|int $x): void { $this->x = $x; } }';
        yield 'Setter with UnionType hint and return void' => ['setter', $code];

        $code = '<?php class A { function _(NameInterface&Name $x): void { $this->x = $x; } }';
        yield 'Setter with IntersectType hint and return void' => ['setter', $code];

        $code = '<?php class A { function _($x): self { $this->x = $x; return $this; } }';
        yield 'Fluent setter with return self' => ['setter', $code];

        $code = '<?php class A { function _($x): static { $this->x = $x; return $this; } }';
        yield 'Fluent setter with return static' => ['setter', $code];

        $code = '<?php class A { function _($x): A { $this->x = $x; return $this; } }';
        yield 'Fluent setter with return __CLASS__' => ['setter', $code];

        $code = '<?php class A { function _($x){ $this->x = (string)$x * 3; } }';
        yield 'Neither setter nor getter' => [null, $code];

        $code = '<?php class A { use TestTrait; }';
        yield 'Not even a method' => [null, $code];
    }

    /**
     * @dataProvider provideExamples
     * @param null|string $expected
     * @param string $code
     * @return void
     */
    //#[DataProvider('provideExamples')] TODO PHPUnit 10
    public function testICanDetectRoleOfMethod(null|string $expected, string $code): void
    {
        $helper = new RoleOfMethodDetector(new SimpleNodeIterator());

        // As all snippets tested are all starting with a "class" definition and contains nothing else as class sibling,
        // all nodes on the 1st level of code being parsed are instances of "Class_" node.
        array_map(static function (Class_ $node) use ($expected, $helper): void {
            foreach ($node->stmts as $sub) {
                self::assertSame($expected, $helper->detects($sub));
            }
        }, (new ParserFactory())->create(ParserFactory::PREFER_PHP7)->parse($code));
    }
}
