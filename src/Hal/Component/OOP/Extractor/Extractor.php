<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Extractor;
use Hal\Component\OOP\Reflected\ReflectedClass\ReflectedAnonymousClass;
use Hal\Component\OOP\Resolver\NameResolver;
use Hal\Component\Token\Tokenizer;


/**
 * Extracts info about classes in one file
 * Remember that one file can contains multiple classes
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Extractor {

    /**
     * @var Searcher
     */
    private $searcher;

    /**
     * @var Result
     */
    private $result;

    /**
     * @var \StdClass
     */
    private $extractors;

    /**
     * @var \Hal\Component\Token\Tokenizer
     */
    private $tokenizer;

    /**
     * Constructor
     */
    public function __construct(Tokenizer $tokenizer) {

        $this->tokenizer = $tokenizer;
        $this->searcher = new Searcher();
        $this->result= new Result;

        $this->extractors = (object) array(
            'class' => new ClassExtractor($this->searcher)
            , 'interface' => new InterfaceExtractor($this->searcher)
            , 'alias' => new AliasExtractor($this->searcher)
            , 'method' => new MethodExtractor($this->searcher)
            , 'call' => new CallExtractor($this->searcher)
        );
    }

    /**
     * Extract infos from file
     *
     * @param $filename
     * @return Result
     */
    public function extract($filename)
    {

        $result = new Result;

        $tokens = $this->tokenizer->tokenize($filename);
        $nameResolver = new NameResolver();

        // default current values
        $class = $interface = $function = $namespace = $method = null;

        $len = sizeof($tokens, COUNT_NORMAL);
        $endAnonymous = 0;
        $mainContextClass = null; // class containing a anonymous class

        for($n = 0; $n < $len; $n++) {

            if($mainContextClass && $n > $endAnonymous) {
                // anonymous class is finished. We back to parent class
                // methods will be added to the main class now
                $class = $mainContextClass;
                $mainContextClass = null;
            }

            $token = $tokens[$n];

            switch($token->getType()) {

                case T_USE:
                    $alias = $this->extractors->alias->extract($n, $tokens);
                    if (null !== $alias->name && null !== $alias->alias) {
                        $nameResolver->pushAlias($alias);
                    }
                    break;

                case T_NAMESPACE:
                    $namespace = '\\'.$this->searcher->getFollowingName($n, $tokens);
                    $this->extractors->class->setNamespace($namespace);
                    $this->extractors->interface->setNamespace($namespace);
                    break;

                case T_INTERFACE:
                    $class = $this->extractors->interface->extract($n, $tokens);
                    $class->setNameResolver($nameResolver);
                    // push class AND in global AND in local class map
                    $this->result->pushClass($class);
                    $result->pushClass($class);
                    break;

                case T_EXTENDS:
                    $i = $n;
                    $parent = $this->searcher->getFollowingName($i, $tokens);
                    $class->setParent(trim($parent));
                    break;

                case T_IMPLEMENTS:
                    $i = $n + 1;
                    $contracts = $this->searcher->getUnder(array('{'), $i, $tokens);
                    $contracts = explode(',', $contracts);
                    $contracts = array_map('trim', $contracts);
                    $class->setInterfaces($contracts);
                    break;

                case T_CLASS:
                    $c = $this->extractors->class->extract($n, $tokens);
                    $c->setNameResolver($nameResolver);
                    // push class AND in global AND in local class map
                    $this->result->pushClass($c);
                    $result->pushClass($c);

                    // PHP 7 and inner classes
                    if($c instanceof ReflectedAnonymousClass) {
                        // avoid to consider anonymous class as main class
                        $p = $n;
                        $endAnonymous = $this->searcher->getPositionOfClosingBrace($p, $tokens);
                        $mainContextClass = $class;

                        // add anonymous class in method
                        if($method) {
                            $method->pushAnonymousClass($c);
                        }
                    }
                    $class = $c;
                    break;

                case T_FUNCTION:
                    if($class) {
                        // avoid closure
                        $next = $tokens[$n + 1];
                        if(T_WHITESPACE != $next->getType()) {
                            continue;
                        }
                        $method = $this->extractors->method->extract($n, $tokens);
                        $method->setNamespace($namespace);
                        $class->pushMethod($method);
                    }
                    break;
            }

        }
        return $result;
    }

};