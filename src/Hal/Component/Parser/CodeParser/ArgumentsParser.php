<?php
namespace Hal\Component\Parser\CodeParser;

use Hal\Component\Parser\Exception\IncorrectSyntaxException;
use Hal\Component\Reflected\Argument;
use Hal\Component\Parser\Resolver\NamespaceResolver;
use Hal\Component\Parser\Searcher;
use Hal\Component\Parser\Token;


class ArgumentsParser
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
        $arguments = array();
        $len = sizeof($tokens);

        // default values
        $type = null;

        for ($i = 0; $i < $len; $i++) {
            $token = $tokens[$i];

            // type
            if ('$' !== $token[0]) {
                $type = $token;
                continue;
            }

            $argument = new Argument($token);
            $argument->setType($type);
            $type = null;

            // default value
            if ($i < $len) {
                $next = $tokens[$i + 1];
                if (Token::T_EQUAL === $next) {
                    $i = $i + 2;
                    // look for default value
                    if ($i == $len) {
                        throw new IncorrectSyntaxException('not default value found for parameter ' . $token);
                    }
                    $value = $tokens[$i];

                    $argument->setDefaultValue($value);
                }
            }
            array_push($arguments, $argument);
        }

        return $arguments;
    }
}