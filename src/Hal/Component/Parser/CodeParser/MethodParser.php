<?php
namespace Hal\Component\Parser\CodeParser;

use Hal\Component\Reflected\Method;
use Hal\Component\Parser\Resolver\NamespaceResolver;
use Hal\Component\Parser\Searcher;

class MethodParser
{

    /**
     * @var Searcher
     */
    private $searcher;

    /**
     * @var NamespaceResolver
     */
    private $namespaceResolver;

    /**
     * CodeParser constructor.
     * @param Searcher $searcher
     * @param NamespaceResolver $namespaceResolver
     */
    public function __construct(Searcher $searcher, NamespaceResolver $namespaceResolver)
    {
        $this->searcher = $searcher;
        $this->namespaceResolver = $namespaceResolver;
    }

    /**
     * @param $tokens
     * @return Method
     */
    public function parse($tokens)
    {
        $method = new Method();
        $method->setTokens($tokens)->setName($tokens[1]);

        // arguments
        $argumentParser = new ArgumentsParser($this->searcher, $this->namespaceResolver);
        $start = $this->searcher->getNext($tokens, 2, '(') + 1;
        $end = $this->searcher->getNext($tokens, $start, ')');
        $method->setArguments($argumentParser->parse(array_splice($tokens, $start, $end - $start)));

        // calls, instanciations
        $callParser = new CallsParser($this->searcher, $this->namespaceResolver);
        $method->setCalls($callParser->parse($tokens));

        // return type
        $returnParser = new ReturnParser($this->searcher, $this->namespaceResolver);
        $method->setReturns($returnParser->parse(array_splice($tokens, 0, sizeof($tokens))));

        return $method;
    }
}