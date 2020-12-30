<?php

namespace Stevebauman\Purify;

use HTMLPurifier_DefinitionCacheFactory;
use HTMLPurifier_DefinitionCache_Serializer;
use HTMLPurifier_DefinitionCache_Null;

/**
 * Responsible for creating definition caches.
 */
class HTMLPurifierDefinitionCacheFactory extends HTMLPurifier_DefinitionCacheFactory
{
    /**
     * Retrieves an instance of global definition cache factory.
     * @param HTMLPurifier_DefinitionCacheFactory $prototype
     * @return HTMLPurifier_DefinitionCacheFactory
     */
    public static function instance($prototype = null)
    {
        static $instance;
        if ($prototype !== null) {
            $instance = $prototype;
        } elseif ($instance === null || $prototype === true) {
            $instance = new HTMLPurifierDefinitionCacheFactory();
            $instance->setup();
        }
        return $instance;
    }

    /**
     * Factory method that creates a cache object based on configuration
     * @param string $type Name of definitions handled by cache
     * @param HTMLPurifier_Config $config Config instance
     * @return mixed
     */
    public function create($type, $config)
    {
        $method = $config->get('Cache.DefinitionImpl');
        if ($method === null) {
            return new HTMLPurifier_DefinitionCache_Null($type);
        }
        if (!empty($this->caches[$method][$type])) {
            return $this->caches[$method][$type];
        }
        if (
            isset($this->implementations[$method]) &&
            class_exists($class = $this->implementations[$method])
        ) {
            $cache = new $class($type);
        } else {
            if ($method != 'Serializer') {
                trigger_error("Unrecognized DefinitionCache $method, using Serializer instead", E_USER_WARNING);
            }
            $cache = new HTMLPurifier_DefinitionCache_Serializer($type);
        }
        foreach ($this->decorators as $decorator) {
            $new_cache = $decorator->decorate($cache);
            // prevent infinite recursion in PHP 4
            unset($cache);
            $cache = $new_cache;
        }
        $this->caches[$method][$type] = $cache;
        return $this->caches[$method][$type];
    }
}

// vim: et sw=4 sts=4
