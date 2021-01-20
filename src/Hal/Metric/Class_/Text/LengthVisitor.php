<?php
namespace Hal\Metric\Class_\Text;

use Hal\Metric\FunctionMetric;
use Hal\Metric\Metrics;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter;

/**
 * @package Hal\Metric\Class_\Text
 */
class LengthVisitor extends NodeVisitorAbstract
{

    /**
     * @var Metrics
     */
    private $metrics;

    /**
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
        if ($node instanceof Stmt\Class_ || $node instanceof Stmt\Function_ || $node instanceof Stmt\Trait_) {
            if ($node instanceof Stmt\Class_ || $node instanceof Stmt\Trait_) {
                $name = (string)(isset($node->namespacedName) ? $node->namespacedName : 'anonymous@' . spl_object_hash($node));
                $classOrFunction = $this->metrics->get($name);
            } else {
                $classOrFunction = new FunctionMetric((string)$node->name);
                $this->metrics->attach($classOrFunction);
            }

            $prettyPrinter = new PrettyPrinter\Standard();
            $code = $prettyPrinter->prettyPrintFile([$node]);

            // count all lines
            $loc = count(preg_split('/\r\n|\r|\n/', $code)) - 1;

            // count and remove multi lines comments
            $cloc = 0;
            if (preg_match_all('!/\*.*?\*/!s', $code, $matches)) {
                foreach ($matches[0] as $match) {
                    $cloc += max(1, count(preg_split('/\r\n|\r|\n/', $match)));
                }
            }
            $code = preg_replace('!/\*.*?\*/!s', '', $code);

            // count and remove single line comments
            $code = preg_replace_callback('!(\'[^\']*\'|"[^"]*")|((?:#|\/\/).*$)!m', function (array $matches) use (&$cloc) {
                if (isset($matches[2])) {
                    $cloc += 1;
                }
                return $matches[1];
            }, $code, -1);

            // count and remove empty lines
            $code = trim(preg_replace('!(^\s*[\r\n])!sm', '', $code));
            $lloc = count(preg_split('/\r\n|\r|\n/', $code));

            // save result
            $classOrFunction
                ->set('cloc', $cloc)
                ->set('loc', $loc)
                ->set('lloc', $lloc);
            $this->metrics->attach($classOrFunction);
        }
    }
}
