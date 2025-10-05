<?php

namespace ARC\Blueprint;

class SchemaRegistry
{
    /**
     * Registered schemas
     *
     * @var array
     */
    private static $schemas = [];

    /**
     * Register a schema
     *
     * @param string $schemaClass
     * @return void
     */
    public static function register($schemaClass)
    {
        if (!in_array($schemaClass, self::$schemas)) {
            self::$schemas[] = $schemaClass;
        }
    }

    /**
     * Get all registered schemas
     *
     * @return array
     */
    public static function all()
    {
        return self::$schemas;
    }

    /**
     * Check if a schema is registered
     *
     * @param string $schemaClass
     * @return bool
     */
    public static function has($schemaClass)
    {
        return in_array($schemaClass, self::$schemas);
    }

    /**
     * Clear all registered schemas
     *
     * @return void
     */
    public static function clear()
    {
        self::$schemas = [];
    }
}
