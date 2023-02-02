<?php
declare(strict_types=1);

namespace Hal\Metric\Class_\Text;

use Hal\Metric\Helper\MetricNameGenerator;
use Hal\Metric\Metric;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter;
use function array_map;
use function array_pad;
use function max;
use function preg_match_all;
use function preg_replace;
use function preg_replace_callback;
use function preg_split;
use function trim;

/**
 * This visitor is building metrics for each ClassLike and functions regarding the length of code.
 * The following metrics are calculated:
 * - loc: count all lines of code, including logical, comments, mixed and empty lines.
 * - cloc: count of commented lines of code. Mixed lines (with both logical and commented) are included.
 * - lloc: count of logical lines of code. Mixed lines (with both logical and commented) are included.
 *
 * As there is no metrics about mixed and empty lines, the total number of lines of code can differ from the sum of the
 * number of commented lines and number of logical lines. Considering the following example:
 * ```php
 * $a = 42; // This is the answer to the great question!
 * ```
 * will have the following metrics: [loc:1, cloc:1, lloc:1].
 */
final class LengthVisitor extends NodeVisitorAbstract
{
    /**
     * @param Metrics $metrics
     * @param PrettyPrinter\Standard $prettyPrinter
     */
    public function __construct(
        private readonly Metrics $metrics,
        private readonly PrettyPrinter\Standard $prettyPrinter
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function leaveNode(Node $node): null|int|Node|array // TODO PHP 8.2: only return null here.
    {
        if (
            !$node instanceof Stmt\Class_
            && !$node instanceof Stmt\Function_
            && !$node instanceof Stmt\Trait_
            //TODO: && !$node instanceof Stmt\Enum_
            //TODO: && !$node instanceof Stmt\Interface_ ??
        ) {
            return null;
        }

        $nodeName = ($node instanceof Stmt\Function_)
            ? MetricNameGenerator::getFunctionName($node)
            : MetricNameGenerator::getClassName($node);
        /** @var Metric $classOrFunction */
        $classOrFunction = $this->metrics->get($nodeName);

        $code = $this->prettyPrinter->prettyPrintFile([$node]);

        // Count all lines.
        $loc = $this->countSplitLines($code) - 1;

        // Count and remove multi lines comments.
        $cloc = 0;
        preg_match_all('!/\*.*?\*/!s', $code, $matches);
        array_map(function (string $commentedCode) use (&$cloc): void {
            $cloc += max(1, $this->countSplitLines($commentedCode));
        }, $matches[0]);
        /** @var string $code subject and replacement are string, so the return remains a string. */
        $code = preg_replace('!/\*.*?\*/!s', '', $code);

        // Count and remove single line comments. New PHP 8: Do not remove PHP Attributes (#[...]).
        /** @var string $code subject and replacement are string, so the return remains a string. */
        $code = preg_replace_callback(
            '!(\'[^\']*\'|"[^"]*")|((?:#[^\[]|//).*$)!m',
            static function (array $matches) use (&$cloc): string {
                [, $logicalCode, $commentedCode] = array_pad($matches, 3, null);
                $cloc += (null !== $commentedCode);
                /** @var string */
                return $logicalCode;
            },
            $code
        );

        // Count and remove empty lines.
        /** @var string $code subject and replacement are string, so the return remains a string. */
        $code = preg_replace('!(^\s*[\r\n])!m', '', $code);
        $code = trim($code);
        $lloc = '' === $code ? 0 : $this->countSplitLines($code);

        // save result
        $classOrFunction->set('cloc', $cloc);
        $classOrFunction->set('loc', $loc);
        $classOrFunction->set('lloc', $lloc);

        return null;
    }

    /**
     * Count the number of lines in the string code given in argument.
     *
     * @param string $code
     * @return int
     */
    private function countSplitLines(string $code): int
    {
        /** @var array<string> $lines */
        $lines = preg_split('/\r\n|\r|\n/', $code);
        return count($lines);
    }
}
