<?php

namespace LunaCMS\Services;

use Exception;

class CacheService
{
    private $redis;

    public function __construct(array $config)
    {
        if (!class_exists('Redis')) {
            throw new Exception("Redis extension not available.");
        }

        try {
            $this->redis = new \Redis();
            $this->redis->connect($config['host'], $config['port']);
        } catch (Exception $e) {
            error_log('Redis initialization failed: ' . $e->getMessage());
            throw new Exception('Could not establish Redis connection.');
        }
    }

    public function get(string $key)
    {
        return $this->redis->get($key);
    }

    public function set(string $key, $value, int $ttl = 0): void
    {
        $this->redis->set($key, $value, $ttl);
    }
}
