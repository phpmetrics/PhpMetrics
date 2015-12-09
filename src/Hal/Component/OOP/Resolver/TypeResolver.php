<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\OOP\Resolver;


/**
 * Type resolver
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class TypeResolver
{

    const TYPE_VOID = 'void';
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOL = 'bool';
    const TYPE_ARRAY = 'array';
    const TYPE_CALLABLE = 'callable';
    const TYPE_NULL = 'null';
    const TYPE_UNKNWON = 'unknown';
    const TYPE_FLUENT_INTERFACE = 'fluent';
    const TYPE_ANONYMOUS_CLASS = 'anonymous@class';

    /**
     * Resolves type of given string
     *
     * @param $string
     * @return string
     */
    public function resolve($string)
    {
        $string = $cased = trim($string, ";\t\n\r\0\x0B");
        $string = strtolower($string);

        if(strlen($string) == 0) {
            return self::TYPE_VOID;
        }

        if(preg_match('!^\d+$!', $string)) {
            return self::TYPE_INTEGER;
        }

        if(preg_match('!^\d+\.\d+$!', $string)) {
            return self::TYPE_FLOAT;
        }

        if('null' == $string) {
            return self::TYPE_NULL;
        }

        if(preg_match('!(^\[|^array\()!', $string)) {
            return self::TYPE_ARRAY;
        }

        if(preg_match('!^new\s+class\s+!', $string, $matches)) {
            return self::TYPE_ANONYMOUS_CLASS;
        }

        if(preg_match('!^(new\s+)(.*?)(\s*[\(;].*|$)!', $cased, $matches)) {
            return $matches[2];
        }

        if(preg_match('!^\$this$!', $string, $matches)) {
            return self::TYPE_FLUENT_INTERFACE;
        }

        if(preg_match('!^(true|false)!', $string, $matches)) {
            return self::TYPE_BOOL;
        }

        if(preg_match('!^function!', $string, $matches)) {
            return self::TYPE_CALLABLE;
        }

        if(preg_match('!^["\']!', $string, $matches)) {
            return self::TYPE_STRING;
        }

        return self::TYPE_UNKNWON;
    }

    /**
     * Check if type is native (not for PHP, but for this resolver)
     *
     * @param $type
     * @return bool
     */
    public function isNative($type) {
        return in_array(strtolower(trim($type)), array(
            self::TYPE_VOID,
            self::TYPE_STRING,
            self::TYPE_INTEGER,
            self::TYPE_FLOAT,
            self::TYPE_ARRAY,
            self::TYPE_CALLABLE,
            self::TYPE_BOOL,
            self::TYPE_NULL,
            self::TYPE_UNKNWON,
            self::TYPE_FLUENT_INTERFACE,
        ));
    }


}