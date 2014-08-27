<?php

namespace Hal\Component\Config;
use Symfony\Component\Config\Definition\Processor;
use \Symfony\Component\Config\Definition\NodeInterface;

class Bar {
    public function foo(array $config) {
        $processor = new Processor();
        return $processor->process($this->tree, $config);
    }
}