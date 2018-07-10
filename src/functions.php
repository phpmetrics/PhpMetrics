<?php

/**
 * Class MyVisitor
 */
class MyVisitor extends \PhpParser\NodeVisitorAbstract
{

    /**
     * @var
     */
    private $callback;

    /**
     * MyVisitor constructor.
     * @param $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(\PhpParser\Node $node)
    {
        call_user_func($this->callback, $node);
    }
}

/**
 * @param $node
 * @param $callback
 */
function iterate_over_node($node, $callback)
{
    /*
         // way 2
         foreach ($node->getSubNodeNames() as $name) {
             $subNode = $node->$name;



             if ($subNode instanceof \PhpParser\Node) {

                 return iterate_over_node($node, $callback);
             }

             if (is_array($subNode)) {
                 foreach ($subNode as $sub) {
                     return iterate_over_node($sub, $callback);
                 }
             }

                 return $callback($node);
         }

         return $callback($node);
    */

    /*
        // way 1
        static $traverser;
        if (!isset($traverser)) {
            $myVisitor = new MyVisitor($callback);
            $traverser = new \PhpParser\NodeTraverser();
            $traverser->addVisitor($myVisitor);
        }
        $traverser->traverse(array($node));
        return;
    */

    // way 1
    $myVisitor = new MyVisitor($callback);
    $traverser = new \PhpParser\NodeTraverser();
    $traverser->addVisitor($myVisitor);
    $traverser->traverse(array($node));
    return;
}

/**
 * @param $node
 * @return string|null
 */
function getNameOfNode($node)
{
    if (is_string($node)) {
        return $node;
    }

    if ($node instanceof \PhpParser\Node\Name\FullyQualified) {
        return (string)$node;
    }
    if ($node instanceof \PhpParser\Node\Expr\New_) {
        return getNameOfNode($node->class);
    }

    if (isset($node->class)) {
        return getNameOfNode($node->class);
    }

    if ($node instanceof \PhpParser\Node\Name) {
        return (string)implode($node->parts);
    }

    if (isset($node->name) && $node->name instanceof \PhpParser\Node\Expr\Variable) {
        return getNameOfNode($node->name);
    }

    if (isset($node->name) && $node->name instanceof \PhpParser\Node\Expr\MethodCall) {
        return getNameOfNode($node->name);
    }

    if ($node instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
        return getNameOfNode($node->var);
    }

    if (isset($node->name) && $node->name instanceof \PhpParser\Node\Expr\BinaryOp) {
        return get_class($node->name);
    }

    if ($node instanceof \PhpParser\Node\Expr\PropertyFetch) {
        return getNameOfNode($node->var);
    }

    if (isset($node->name) && !is_string($node->name)) {
        return getNameOfNode($node->name);
    }

    if (isset($node->name) && null === $node->name) {
        return 'anonymous@' . spl_object_hash($node);
    }

    if (isset($node->name)) {
        return (string)$node->name;
    }

    return null;
}

/**
 * @param $src
 * @param $dst
 */
function recurse_copy($src, $dst)
{
    $dir = opendir($src);
    if (!file_exists($dst)) {
        mkdir($dst);
    }
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

/**
 * @return string
 */
function getVersion()
{
    return 'v2.4.1';
}
