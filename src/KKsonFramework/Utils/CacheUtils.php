<?php

namespace KKsonFramework\Utils;

class CacheUtils
{

    private static $cache;

    private static function getInstance()
    {
        if(self::$cache == null) {
            self::$cache = new Cache();
        }
        return self::$cache;
    }

    public static function has($k)
    {
        return self::getInstance()->has($k);
    }

    public static function put($k, $v)
    {
        self::getInstance()->put($k,$v);
    }

    public static function get($k)
    {
        return self::getInstance()->get($k);
    }

    public static function remove($k)
    {
        self::getInstance()->remove($k);
    }
}