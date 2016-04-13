<?php
namespace Hal\Component\Parser\CodeParser;

use Hal\Component\Parser\Exception\IncorrectSyntaxException;
use Hal\Component\Reflected\Argument;
use Hal\Component\Reflected\ReturnedValue;
use Hal\Component\Parser\Resolver\NamespaceResolver;
use Hal\Component\Parser\Searcher;
use Hal\Component\Parser\Token;


class ReturnParser
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
     * @return array
     */
    public function parse($tokens)
    {
        $returns = array();
        $len = sizeof($tokens);

        // PHP 7 return
        $closingParenthesis = $this->searcher->getNext($tokens, 0, Token::T_PARENTHESIS_CLOSE);
        if($closingParenthesis + 2 <= sizeof($tokens)) {
            if(Token::T_FUNCTION_RETURN === $tokens[$closingParenthesis + 1]) {
                $class = $this->namespaceResolver->resolve($tokens[$closingParenthesis + 2]);
                array_push($returns, new ReturnedValue($class));
            }
        }

        // returns in code
        for ($i = 0; $i < $len; $i++) {
            $token = $tokens[$i];
            $len = sizeof($tokens);
            for($i = 0; $i < $len; $i++) {

                $token = $tokens[$i];

                if(Token::T_RETURN === $token) {

                    // void ("return;)
                    if($i >= $len) {
                        array_push($returns, new ReturnedValue(ReturnedValue::VOID));
                        continue;
                    }

                    // return with value
                    $next = $tokens[$i + 1];
                    if(Token::T_NEW === $next) {
                        if($next + 1 > sizeof($tokens)) {
                            throw new IncorrectSyntaxException('"return new" without classname');
                        }
                        $classname = $tokens[$i + 2];
                        array_push($returns, new ReturnedValue($this->namespaceResolver->resolve($classname)));
                    }

                }
            }
        }
        return $returns;
    }
}