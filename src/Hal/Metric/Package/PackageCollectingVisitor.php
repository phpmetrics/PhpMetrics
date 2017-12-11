<?php

namespace Hal\Metric\Package;

use Hal\Metric\Metrics;
use Hal\Metric\PackageMetric;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;

class PackageCollectingVisitor extends NodeVisitorAbstract
{
    /** @var string */
    private $namespace = '';

    /** @var Metrics */
    private $metrics;

    public function __construct(Metrics $metrics)
    {
        $this->metrics = $metrics;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            $this->namespace = (string) $node->name;
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Class_ || $node instanceof Interface_ || $node instanceof Trait_) {
            $package = $this->namespace;

            $docComment = $node->getDocComment();
            $docBlockText = $docComment ? $docComment->getText() : '';
            if (preg_match('/^\s*\* @package (.*)/m', $docBlockText, $matches)) {
                $package = $matches[1];
            }
            if (preg_match('/^\s*\* @subpackage (.*)/m', $docBlockText, $matches)) {
                $package = $package . '\\' . $matches[1];
            }

            $packageName = $package . '\\';
            if (! $packageMetric = $this->metrics->get($packageName)) {
                $packageMetric = new PackageMetric($packageName);
                $this->metrics->attach($packageMetric);
            }
            /* @var PackageMetric $packageMetric */
            $elementName = isset($node->namespacedName) ? $node->namespacedName : 'anonymous@'.spl_object_hash($node);
            $elementName = (string) $elementName;
            $packageMetric->addClass($elementName);

            $this->metrics->get($elementName)->set('package', $packageName);
        }
    }
}
