<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\OOP\Extractor;
use Hal\OOP\Reflected\ReflectedArgument;
use Hal\OOP\Reflected\ReflectedClass;
use Hal\OOP\Reflected\ReflectedMethod;
use Hal\Token\Token;


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
     * @var StdClass
     */
    private $extractors;

    /**
     * Constructor
     */
    public function __construct() {

        $this->searcher = new Searcher();

        $this->extractors = (object) array(
            'class' => new ClassExtractor($this->searcher)
            , 'method' => new MethodExtractor($this->searcher)
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
        $tokens = token_get_all(file_get_contents($filename));

        // default current values
        $class = null;
        $function = null;

        $len = sizeof($tokens, COUNT_NORMAL);
        for($n = 0; $n < $len; $n++) {

            $token = new Token($tokens[$n]);

            switch($token->getType()) {

                case T_NAMESPACE:
                    $namespace = '\\'.$this->searcher->getFollowingName($n, $tokens);
                    $this->extractors->class->setNamespace($namespace);
                    break;

                case T_CLASS:
                    $class = $this->extractors->class->extract($n, $tokens);
                    $result->pushClass($class);
                    break;

                case T_FUNCTION:
                    $method = $this->extractors->method->extract($n, $tokens);
                    $class->pushMethod($method);
                    break;
            }

        }
        return $result;
    }

};