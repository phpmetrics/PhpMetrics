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
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class ClassExtractor implements ExtractorInterface {

    /**
     * @var Searcher
     */
    private $searcher;

    /**
     * @var string
     */
    private $namespace;

    /**
     * Constructor
     *
     * @param Searcher $searcher
     */
    public function __construct(Searcher $searcher)
    {
        $this->searcher = $searcher;
    }

    /**
     * Extract class from position
     *
     * @param $n
     * @param array $tokens
     * @return ReflectedMethod
     */
    public function extract(&$n, $tokens)
    {
        $classname = $this->searcher->getFollowingName($n, $tokens);
        return new ReflectedClass($this->namespace, $classname);
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = (string) $namespace;
    }

};