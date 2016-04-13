<?php
namespace Hal\Component\Parser\Resolver;

use Hal\Component\Parser\Exception\IncorrectSyntaxException;
use Hal\Component\Token\Token;

class NamespaceResolver
{

    /**
     * @var array
     */
    private $namespaces;

    /**
     * @var string
     */
    private $currentNamespace = '\\';

    /**
     * @param $tokens
     */
    public function __construct($tokens)
    {

        $len = sizeof($tokens);
        for ($i = 0; $i < $len; $i++) {

            // stop when class or function is detected
            if (in_array($tokens[$i], array(Token::T_CLASS, Token::T_FUNCTION))) {
                break;
            }

            $next = $i + 1;

            // "namespace" token
            if (Token::T_NAMESPACE === $tokens[$i]) {
                if (!isset($tokens[$next])) {
                    throw new IncorrectSyntaxException;
                }
                $this->currentNamespace = '\\' . $tokens[$next];
                continue;
            }

            // "use" token
            if (Token::T_USE === $tokens[$i]) {
                $i = $i + 1;
                if (!isset($tokens[$next])) {
                    throw new IncorrectSyntaxException;
                }

                // name of class
                $name = $alias = $tokens[$next];

                // alias
                if (isset($tokens[$next + 1], $tokens[$next + 2])
                    && Token::T_AS == $tokens[$next + 1]
                ) {
                    $i = $i + 2;
                    $alias = $tokens[$next + 2];
                }

                $this->namespaces[$alias] = $name;
                continue;
            }

        }
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->namespaces;
    }

    /**
     * @return string
     */
    public function getCurrentNamespace()
    {
        return $this->currentNamespace;
    }

    /**
     * @param $alias
     * @return string
     */
    public function resolve($alias)
    {
        if (preg_match('!^(parent|static|self)$!i', $alias)) {
            return $alias;
        }
        if ($this->has($alias) && '\\' == $this->namespaces[$alias][0]) {
            return $this->namespaces[$alias];
        }
        if ('\\' === $alias[0]) {
            return $alias;
        }
        if ($this->has('\\' . $alias)) {
            return '\\' . $alias;
        }
        if ($this->has($alias)) {
            return sprintf('%s\\%s', $this->currentNamespace, $this->namespaces[$alias]);
        }
        return sprintf('%s\\%s', $this->currentNamespace, $alias);
    }

    /**
     * @param $alias
     * @return bool
     */
    public function has($alias)
    {
        return isset($this->namespaces[$alias]);
    }
}