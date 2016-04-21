<?php
namespace Hal\Component\Parser\CodeParser;

use Hal\Component\Reflected\Attribute;
use Hal\Component\Reflected\Klass;
use Hal\Component\Reflected\KlassAnonymous;
use Hal\Component\Reflected\KlassInterface;
use Hal\Component\Parser\Resolver\NamespaceResolver;
use Hal\Component\Parser\Searcher;
use Hal\Component\Token\Token;

class ClassParser
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
     * @return Klass
     */
    public function parse($tokens)
    {
        // anonymous class
        if (Token::T_NEW === $tokens[0]) {
            $class = new KlassAnonymous;
            $class->setName($tokens[2]);
        } elseif (Token::T_INTERFACE === $tokens[1]) {
            $class = new KlassInterface();
            $class->setName($tokens[2]);
        } else {
            $class = new Klass();
            $class->setName($tokens[2]);
        }


        $functions = $attributes = array();
        $len = sizeof($tokens);

        // default values
        $isStatic = false;
        $visibility = Token::T_VISIBILITY_PUBLIC;

        for ($i = 1; $i < $len; $i++) {

            $token = $tokens[$i];

            // static
            if (Token::T_STATIC === $token) {
                $isStatic = true;
            }

            // visibility
            if (in_array($token, array(Token::T_VISIBILITY_PRIVATE, Token::T_VISIBILITY_PROTECTED, Token::T_VISIBILITY_PUBLIC))) {
                $visibility = $token;
            }

            // method
            if (Token::T_FUNCTION === $token) {
                $functionStart = $i;
                $functionEnd = $this->searcher->getPositionOfClosingBrace($tokens, $i);
                $parser = new MethodParser($this->searcher, $this->namespaceResolver);
                $method = $parser->parse(array_slice($tokens, $functionStart, ($functionEnd - $functionStart) + 1));

                // attribute external informations
                $method->setIsStatic($isStatic)->setVisibility($visibility);
                $isStatic = false;
                $visibility = Token::T_VISIBILITY_PUBLIC;

                $functions[$method->getName()] = $method;
                $i = $functionEnd;
            }

            // attribute
            if ('$' === $token[0]) {
                $attribute = new Attribute($token, $visibility, $isStatic);
                array_push($attributes, $attribute);
            }

            // extends
            if (Token::T_EXTENDS === $token) {
                $brace = $this->searcher->getNext($tokens, $i, Token::T_BRACE_OPEN);
                $parents = array_slice($tokens, $i + 1, ($brace - $i) - 1);
                // exclude implements
                $split = preg_split('!implements!i', implode(',', $parents));
                $parents = array_filter(explode(',', $split[0]));
                foreach ($parents as $p => $name) {
                    $parents[$p] = $this->namespaceResolver->resolve($name);
                }
                $class->setParents($parents);
            }
        }

        $class
            ->setTokens($tokens)
            ->setMethods($functions)
            ->setAttributes($attributes)
            ->setNamespace($this->namespaceResolver->getCurrentNamespace());

        return $class;
    }
}