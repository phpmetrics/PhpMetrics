<?php
abstract class Model
{
    use \Phan\Memoize;

    /**
     * @return Schema
     * The schema for this model
     */
    abstract public static function createSchema() : Schema;

    /**
     * @return Schema
     * Get the schema for this model
     */
    public static function schema() : Schema
    {
        return self::memoizeStatic(get_called_class() . '::' . __METHOD__, function () {
            return static::createSchema();
        });
    }
}