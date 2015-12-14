<?php
class MyClass {
    /**
     * Register a stream filter.
     *
     * @param   string  $name         Filter name.
     * @param   mixed   $class        Class name or instance.
     * @param   bool    $overwrite    Overwrite filter if already exists or
     *                                not. Given by self::*OVERWRITE constants.
     * @return  bool
     * @throws  \Hoa\Stream\Filter\Exception
     */
    public static function register(
        $name,
        $class,
        $overwrite = self::DO_NOT_OVERWRITE
    ) {
        if ($overwrite === self::DO_NOT_OVERWRITE &&
            true       === self::isRegistered($name)) {
            throw new Exception('Filter %s is already registered.', 0, $name);
        }

        if (empty($name)) {
            throw new Exception('Filter name cannot be empty.', 1);
        }

        if (is_object($class)) {
            $class = get_class($class);
        }

        return stream_filter_register($name, $class);
    }
}