<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Extractor;
use Hal\Component\Token\Token;
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
     * @var StdClass
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

        // default current values
        $class = null;
        $function = null;
        $mapOfAliases = array();

        $len = sizeof($tokens, COUNT_NORMAL);
        for($n = 0; $n < $len; $n++) {

            $token = new Token($tokens[$n]);

            switch($token->getType()) {

                case T_USE:
                    $alias = $this->extractors->alias->extract($n, $tokens);
                    $mapOfAliases[$alias->alias] = $alias->name;
                    $class && $class->setAliases($mapOfAliases);
                    break;

                case T_PAAMAYIM_NEKUDOTAYIM:
                case T_NEW:
                    if($class) {
                        $class->pushDependency($this->extractors->call->extract($n, $tokens));
                    }
                    break;

                case T_NAMESPACE:
                    $namespace = '\\'.$this->searcher->getFollowingName($n, $tokens);
                    $this->extractors->class->setNamespace($namespace);
                    break;

                case T_CLASS:
                    $class = $this->extractors->class->extract($n, $tokens);
                    $class->setAliases($mapOfAliases);
                    // push class AND in global AND in local class map
                    $this->result->pushClass($class);
                    $result->pushClass($class);
                    break;

                case T_FUNCTION:
                    if($class) {
                        // avoid closure
                        $next = new Token($tokens[$n + 1]);
                        if(T_WHITESPACE != $next->getType()) {
                            continue;
                        }
                        $method = $this->extractors->method->extract($n, $tokens);
                        $class->pushMethod($method);
                    }
                    break;
            }

        }
        return $result;
    }

};