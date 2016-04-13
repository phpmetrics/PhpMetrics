<?php
namespace Hal\Component\Parser;

use Hal\Component\Parser\CodeParser\ClassParser;
use Hal\Component\Parser\CodeParser\MethodParser;
use Hal\Component\Parser\Resolver\NamespaceResolver;
use Hal\Component\Token\Token;

class CodeParser
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
     * @return Result
     */
    public function parse(array $tokens)
    {

        $classes = array();
        $functions = array();
        $anotherTokens = array();

        // store initial values
        $isAbstract = false;

        // we group tokens by class
        $len = sizeof($tokens);
        for ($i = 0; $i < $len; $i++) {
            $token = $tokens[$i];

            if (Token::T_ABSTRACT === $token) {
                $isAbstract = true;
                continue;
            }

            if (Token::T_CLASS === $token || Token::T_INTERFACE === $token) {
                $classStart = $i;
                $classEnd = $this->searcher->getPositionOfClosingBrace($tokens, $i);
                // we need to start tokens from previous token because of php7 anonymous classes
                // example: "new class{"
                $tokensOfClass = array_slice($tokens, max(0, $classStart - 1), ($classEnd - $classStart) + 1);

                $parser = new ClassParser($this->searcher, $this->namespaceResolver);
                $class = $parser->parse($tokensOfClass);
                $class->setIsAbstract($isAbstract);
                array_push($classes, $class);

                $i = $classEnd;
                $isAbstract = false;
                continue;
            }
            array_push($anotherTokens, $tokens[$i]);
        }

        // another code (functions, direct calls...
        $len = sizeof($anotherTokens);
        for ($i = 0; $i < $len; $i++) {
            $token = $anotherTokens[$i];
            if (Token::T_FUNCTION === $token) {
                $functionStart = $i;
                $functionEnd = $this->searcher->getPositionOfClosingBrace($anotherTokens, $i);
                $parser = new MethodParser($this->searcher, $this->namespaceResolver);
                $function = $parser->parse(array_slice($anotherTokens, $functionStart, ($functionEnd - $functionStart) + 1));
                array_push($functions, $function);

                continue;
            }
        }

        $result = new Result;
        $result->setClasses($classes)->setFunctions($functions);

        return $result;
    }
}