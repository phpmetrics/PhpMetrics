<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Resolver;


/**
 * Alias (and namepsace) resolver
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class NameResolver
{

    /**
     * @var array
     */
    private $aliases = array();

    /**
     * Resolve name of class
     *
     * @param string $name
     * @param string $currentNamespace
     * @return string
     */
    public function resolve($name, $currentNamespace = '\\')
    {
        // already namespaced
        if ('\\' == $name[0]) {
            return $name;
        }

        // use xxx as yyy
        if (isset($this->aliases[$name])) {
            return $this->aliases[$name];
        }

        // use xxx;
        foreach ($this->aliases as $alias => $nothing) {
            $parts = preg_split('![^\w]!', $alias);
            $last = $parts[sizeof($parts, COUNT_NORMAL) - 1];
            if ($last === $name) {
                return $alias;
            }
        }

        return rtrim($currentNamespace, '\\').'\\'.$name;
        return $currentNamespace.$name;

    }

    /**
     * Push alias
     *
     * @param \StdClass $alias
     * @return $this
     */
    public function pushAlias(\StdClass $alias) {
        $this->aliases[$alias->alias] = $alias->name;
        return $this;
    }
}