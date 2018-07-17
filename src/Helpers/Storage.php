<?php

namespace Bonnier\WP\SoMe\Helpers;

class Storage
{
    const GROUP = 'WP_BONNIER_SOCIALMEDIA';

    public static function get(string $key)
    {
        $key = hash('md5', $key);
        if ($data = wp_cache_get($key, self::GROUP)) {
            return unserialize($data);
        }

        return null;
    }

    public static function set(string $key, $value, int $expire = 0): bool
    {
        $key = hash('md5', $key);
        return wp_cache_set($key, serialize($value), self::GROUP, $expire);
    }

    public static function remember(string $key, callable $callable, int $expire = 0)
    {
        if ($data = self::get($key)) {
            return $data;
        }
        $value = $callable();
        self::set($key, $value, $expire);
        return $value;
    }

    public static function rememberForever($key, $callable)
    {
        return self::remember($key, $callable, 0);
    }
}
