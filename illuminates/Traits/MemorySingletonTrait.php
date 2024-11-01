<?php

namespace Illuminate\Traits;

trait MemorySingletonTrait
{
    /**
     * Retrive instance
     *
     * @return static
     */
    protected static $memories = [];

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (isset(self::$memories[static::class])) {
            return self::$memories[static::class];
        }

        $class = new static();
        self::$memories[static::class] = $class;

        return $class;
    }
}
