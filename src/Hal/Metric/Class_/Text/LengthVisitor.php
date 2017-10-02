<?php
/**
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Metric\Class_\Text;

use Hal\Metric\FunctionMetric;
use Hal\Metric\Helper\MetricClassNameGenerator;
use Hal\Metric\MetricsVisitorTrait;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter;

/**
 * Class LengthVisitor
 * Calculates the number of lines of code of the parsed project.
 *
 * @package Hal\Metric\Class_\Text
 */
class LengthVisitor extends NodeVisitorAbstract
{
    use MetricsVisitorTrait;

    /**
     * Executed when leaving the traversing of the node. Used to calculates the following elements:
     * - Number of total lines of code
     * - Number of logical lines of code
     * - Number of commented lines of code
     * @param Node $node The current node to leave to make the analysis.
     * @return void
     */
    public function leaveNode(Node $node)
    {
        if (!($node instanceof Stmt\Class_ || $node instanceof Stmt\Function_ || $node instanceof Stmt\Trait_)) {
            return;
        }

        if ($node instanceof Stmt\Function_) {
            $classOrFunction = new FunctionMetric($node->name);
            $this->metrics->attach($classOrFunction);
        } else {
            $classOrFunction = $this->metrics->get(MetricClassNameGenerator::getName($node));
        }

        $code = (new PrettyPrinter\Standard())->prettyPrintFile([$node]);

        // Methods have to be called in this order because of the way to count all kind of code lines.
        $classOrFunction
            ->set('loc', $this->countLinesOfCode($code))
            ->set('cloc', $this->countCommentedLinesOfCode($code))
            ->set('lloc', $this->countLogicalLinesOfCode($code));
        $this->metrics->attach($classOrFunction);
    }

    /**
     * Counts the number of lines of code from the given code snippet.
     * @param string $code The code snippet to analyze.
     * @return int The number of lines of code the snippet has.
     */
    private function countLinesOfCode($code)
    {
        return \count(\preg_split('/\r\n|\r|\n/', $code)) - 1;
    }

    /**
     * Counts the number of commented lines of code from the given code snippet.
     * Also remove those lines and/or comments from the input snippet.
     * @param string $code The code snippet to analyze and modify to remove the comments counted.
     * @return int The number of commented lines of code the snippet has.
     */
    private function countCommentedLinesOfCode(&$code)
    {
        // Count multi-lines comments.
        $cLoc = 0;
        if (\preg_match_all('!/\*.*?\*/!s', $code, $matches)) {
            foreach ($matches[0] as $match) {
                $cLoc += \max(1, \count(\preg_split('/\r\n|\r|\n/', $match)));
            }
        }
        // Remove multi-lines comments.
        $code = \preg_replace('!/\*.*?\*/!s', '', $code);

        // Count and remove single line comments.
        $incrementCLoc = function (array $matches) use (&$cLoc) {
            $cLoc += isset($matches[2]);
            return $matches[1];
        };
        $code = \preg_replace_callback('!(\'[^\']*\'|"[^"]*")|((?:#|\/\/).*$)!m', $incrementCLoc, $code, -1);

        return $cLoc;
    }

    /**
     * Counts the number of logical lines of code from the given code snippet.
     * Also remove the empty lines which are not considered as logical.
     * NOTE: The input code snippet must have been already trimmed from comments.
     * @param string $code The code snippet to analyze and modify to remove the empty lines.
     * @return int The number of logical lines of code the snippet has.
     */
    private function countLogicalLinesOfCode(&$code)
    {
        // Remove empty lines.
        $code = \trim(\preg_replace('!(^\s*[\r\n])!m', '', $code));

        // Count the number of lines that left from all trims.
        return \count(\preg_split('/\r\n|\r|\n/', $code));
    }
}
