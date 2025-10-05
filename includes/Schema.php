<?php

namespace ARC\Blueprint;

abstract class Schema
{
    /**
     * The Collection class reference
     *
     * @var string
     */
    protected $collection;

    /**
     * Register this schema with a key
     *
     * @param string $key Schema key (lowercase with underscores only)
     * @return void
     * @throws \InvalidArgumentException
     */
    public static function register($key)
    {
        // Validate key format
        if (!preg_match('/^[a-z_]+$/', $key)) {
            throw new \InvalidArgumentException(
                "Schema key must be lowercase letters and underscores only. Got: {$key}"
            );
        }

        SchemaRegistry::register($key, static::class);
    }
}
