<?php

namespace ARC\Blueprint;

abstract class Schema
{
    /**
     * The Eloquent model class reference
     *
     * @var string
     */
    protected $model;

    /**
     * Register this schema
     *
     * @return void
     */
    public static function register()
    {
        SchemaRegistry::register(static::class);
    }
}
