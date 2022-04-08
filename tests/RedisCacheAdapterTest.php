<?php

declare(strict_types=1);

namespace Mvc4us\Cache\Tests;

use Mvc4us\Cache\Redis\RedisCacheAdapter;
use PHPUnit\Framework\TestCase;

class RedisCacheAdapterTest extends TestCase
{

    public function testPrefix()
    {
    }

    public function testSetter()
    {
    }

    public function testGetter()
    {
    }

    public function testClear()
    {
    }

    public function testNotFound()
    {
        $redis = new RedisCacheAdapter(REDIS_HOST, REDIS_PORT, REDIS_AUTH);
        $result = $redis->notFound();
        $this->assertTrue($result);
    }

    public function testFound()
    {
        $redis = new RedisCacheAdapter(REDIS_HOST, REDIS_PORT, REDIS_AUTH);
        $result = $redis->found();
        $this->assertFalse($result);
    }

    public function testHas()
    {
        $redis = new RedisCacheAdapter(REDIS_HOST, REDIS_PORT, REDIS_AUTH);
        $key = 'RedisCacheKey';
        $value = 'RedisCacheValue';
        $ttl = 30;
        $redis->set($key, $value, $ttl);
        $result = $redis->has($key);
        $this->assertTrue($result);
    }
}

