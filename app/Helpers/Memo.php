<?php
namespace App\Helpers;

class Memo
{
    private static $data;

    public static function use(string $key, callable $predicate)
    {
        static::$data ??= [];
        if (isset(static::$data[$key])) {
            return static::$data[$key];
        }
        static::$data[$key] = $predicate();
        return static::$data[$key];
    }

    public static function invalidate($key)
    {
        if (isset(static::$data[$key])) {
            unset(static::$data[$key]);
        }
    }

    public static function flush()
    {
        static::$data = [];
    }
}