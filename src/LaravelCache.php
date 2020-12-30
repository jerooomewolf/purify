<?php

namespace Stevebauman\Purify;

use HTMLPurifier_DefinitionCache;
use Illuminate\Support\Facades\Cache;

class LaravelCache extends HTMLPurifier_DefinitionCache
{
    private $tag = '.laravelcache';

    /**
     * @param HTMLPurifier_Definition $def
     * @param HTMLPurifier_Config $config
     * @return int|bool
     */
    public function add($def, $config)
    {
        if (!$this->checkDefType($def)) {
            return;
        }

        $key = $this->generateCacheKey($config);

        if (Cache::has($key)) {
            return false;
        }

        return Cache::put($key, serialize($def));
    }

    /**
     * @param HTMLPurifier_Definition $def
     * @param HTMLPurifier_Config $config
     * @return int|bool
     */
    public function set($def, $config)
    {
        if (!$this->checkDefType($def)) {
            return;
        }

        $key = $this->generateCacheKey($config);

        return Cache::put($key, serialize($def));
    }

    /**
     * @param HTMLPurifier_Definition $def
     * @param HTMLPurifier_Config $config
     * @return int|bool
     */
    public function replace($def, $config)
    {
        if (!$this->checkDefType($def)) {
            return;
        }

        $key = $this->generateCacheKey($config);

        if (!Cache::has($key)) {
            return false;
        }

        return Cache::put($key, serialize($def));
    }

    /**
     * @param HTMLPurifier_Config $config
     * @return bool|HTMLPurifier_Config
     */
    public function get($config)
    {
        $key = $this->generateCacheKey($config);

        if (!Cache::has($key)) {
            return false;
        }

        return unserialize(Cache::get($key));
    }

    /**
     * @param HTMLPurifier_Config $config
     * @return bool
     */
    public function remove($config)
    {
        $key = $this->generateCacheKey($config);

        if (!Cache::has($key)) {
            return false;
        }

        return Cache::forget($key);
    }

    /**
     * @param HTMLPurifier_Config $config
     * @return bool
     */
    public function flush($config)
    {
        return true;
    }

    /**
     * @param HTMLPurifier_Config $config
     * @return bool
     */
    public function cleanup($config)
    {
        return true;
    }

    public function generateCacheKey($config)
    {
        return $this->generateKey($config)  . $this->tag;
    }
}
