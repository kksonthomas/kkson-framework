<?php

namespace KKsonFramework\Utils;

class Cache
{
    private static $staticCache = null;

    /**
     * @return Cache
     */
    public static function getInstance() {
        if(!self::$staticCache) {
            self::$staticCache = new Cache();
        }
        return self::$staticCache;
    }

    private $cache = [];
    public function has($k)
    {
        return isset($this->cache[$k]);
    }

    public function put($k, $v)
    {
        $this->cache[$k] = $v;
    }

    public function get($k)
    {
        return $this->cache[$k];
    }

    public function remove($k)
    {
        if ($this->has($k)) {
            unset($this->cache[$k]);
        }
    }
}