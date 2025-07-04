<?php

namespace Hal\Component\Ast;

use PhpParser\NodeTraverser as Mother;

class Php8NodeTraverser extends Mother
{

    public function __construct()
    {
        parent::__construct();
    }
}
