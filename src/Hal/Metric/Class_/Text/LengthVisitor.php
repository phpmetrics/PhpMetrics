<?php
namespace Hal\Metric\Class_\Text;

use Hal\Metric\FunctionMetric;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter;

/**
 * Class LengthVisitor
 * @package Hal\Metric\Class_\Text
 */
class LengthVisitor extends NodeVisitorAbstract
{

    /**
     * @var Metrics
     */
    private $metrics;

    /**
     * ClassEnumVisitor constructor.
     * @param Metrics $metrics
     */
    public function __construct(Metrics $metrics)
    {
        $this->metrics = $metrics;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Stmt\Class_ || $node instanceof Stmt\Function_) {

            if ($node instanceof Stmt\Class_) {
                $classOrFunction = $this->metrics->get($node->namespacedName->toString());
            } else {
                $classOrFunction = new FunctionMetric($node->name);
                $this->metrics->attach($classOrFunction);
            }


            $prettyPrinter = new PrettyPrinter\Standard();
            $code = $prettyPrinter->prettyPrintFile(array($node));

            // count all lines
            $loc = sizeof(preg_split('/\r\n|\r|\n/', $code)) - 1;

            // count and remove multi lines comments
            $cloc = 0;
            if (preg_match_all('!/\*.*?\*/!s', $code, $matches)) {
                foreach ($matches[0] as $match) {
                    $cloc += max(1, sizeof(preg_split('/\r\n|\r|\n/', $match)));
                }
            }
            $code = preg_replace('!/\*.*?\*/!s', '', $code);

            // count and remove single line comments
            $code = preg_replace('!(\s*?//.+\n)!', "\n", $code, -1, $nbCommentsSingleLine);
            $cloc += $nbCommentsSingleLine;

            // count and remove empty lines
            $code = trim(preg_replace('!(^\s*[\r\n])!sm', '', $code));
            $lloc = sizeof(preg_split('/\r\n|\r|\n/', $code));

            // save result
            $classOrFunction
                ->set('cloc', $cloc)
                ->set('loc', $loc)
                ->set('lloc', $lloc);
            $this->metrics->attach($classOrFunction);
        }
    }
}
