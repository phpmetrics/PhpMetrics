<?php
namespace Hal\Component\Parser\CodeParser;

use Hal\Component\Parser\Exception\IncorrectSyntaxException;
use Hal\Component\Reflected\Call;
use Hal\Component\Reflected\Klass;
use Hal\Component\Parser\Resolver\NamespaceResolver;
use Hal\Component\Parser\Searcher;
use Hal\Component\Token\Token;

class CallsParser
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
     * @return Klass[]
     */
    public function parse($tokens)
    {
        $calls = array();
        $len = sizeof($tokens);

        for ($i = 0; $i < $len; $i++) {
            $token = $tokens[$i];

            // T_PAAMAYIM_NEKUDOTAYIM
            if (preg_match('!(.*)::(.*)!', $token, $matches)) {
                list(, $className, $methodName) = $matches;
                $call = new Call($this->namespaceResolver->resolve($className), $methodName);
                $call->setIsStatic(true);

                // call on itself
                if (preg_match('!(self|static)!i', $className)) {
                    $call->setIsItself(true);
                }

                // call parent
                if (preg_match('!parent!i', $className)) {
                    $call->setIsParent(true);
                }

                array_push($calls, $call);
                continue;
            }

            // $instance->foo();
            if (Token::T_NEW == $token) {
                if ($i == $len) {
                    throw new IncorrectSyntaxException('"new" is not followed by classname');
                }

                // followed by ")->"
                // (new Foo)->bar()
                $next = $tokens[$i + 1];
                $nextAfter = $i + 2 <= $len ? $tokens[$i + 2] : null;
                $nextAfterAfter = $i + 3 <= $len - 1 ? $tokens[$i + 3] : null;
                if (Token::T_PARENTHESIS_CLOSE === $nextAfter && preg_match('!^\-\>(.*)!', $nextAfterAfter, $matches)) {
                    $className = $next;
                    $methodName = $matches[1];
                    $call = new Call($this->namespaceResolver->resolve($className), $methodName);
                    array_push($calls, $call);
                    $i = $i + 2;
                    continue;
                }

                $className = $next;
                $methodName = 'unknown';
                $call = new Call($this->namespaceResolver->resolve($className), $methodName);
                array_push($calls, $call);
            }

            // $this->foo();
            if(preg_match('!^\$this\->(.+)!', $token, $matches)) {
                list(, $methodName) = $matches;
                // next token should be "("
                if(!isset($tokens[$i + 1])) {
                    continue;
                }
                if(Token::T_PARENTHESIS_OPEN === $tokens[$i + 1]) {
                    $call = new Call(null, $methodName);
                    $call->setIsItself(true);
                    array_push($calls, $call);
                }
            }
        }
        return $calls;
    }
}