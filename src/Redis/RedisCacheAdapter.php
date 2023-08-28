<?php
declare(strict_types=1);

namespace Mvc4us\Cache\Redis;

use Mvc4us\Cache\Adapter\AbstractAdapter;
use Mvc4us\Cache\Adapter\CacheException;
use Mvc4us\Cache\Adapter\InvalidArgumentException;
use Mvc4us\Cache\Adapter\NotFoundException;
use Predis\ClientInterface;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

/**
 * @author erdem
 */
class RedisCacheAdapter extends AbstractAdapter
{
    public const RESERVED_CHARACTERS = '{}()\@:';

    use RedisTrait;

    /**
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     * @throws \Mvc4us\Cache\Exception\CacheException
     */
    public function __construct(
        private string $dsn,
        private array $options = [],
        string $namespace = '',
        \DateInterval|int|null $defaultLifetime = null
    ) {
        parent::__construct($namespace, $defaultLifetime);
        $this->initialize($this->dsn, $this->options);
    }

    /**
     * @inheritDoc
     * @throws \RedisException
     */
    public function isConnected(): bool
    {
        return $this->redis->isConnected();
    }

    /**
     * @inheritDoc
     */
    public function reConnect(): void
    {
        $this->initialize($this->dsn, $this->options);
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->doGet($key, null, 'mixed', $default);
    }

    /**
     * @inheritDoc
     */
    public function getBool(string $key, ?bool $default = null): bool
    {
        return $this->doGet($key, null, 'bool', $default);
    }

    /**
     * @inheritDoc
     */
    public function getInt(string $key, ?int $default = null): int
    {
        return $this->doGet($key, null, 'int', $default);
    }

    /**
     * @inheritDoc
     */
    public function getFloat(string $key, ?float $default = null): float
    {
        return $this->doGet($key, null, 'float', $default);
    }

    /**
     * @inheritDoc
     */
    public function getString(string $key, ?string $default = null): string
    {
        return $this->doGet($key, null, 'string', $default);
    }

    /**
     * @inheritDoc
     */
    public function getArray(string $key, ?array $default = null): array
    {
        return $this->doGet($key, null, 'array', $default);
    }

    /**
     * @inheritDoc
     */
    public function getObject(string $key, object|string|null $default): mixed
    {
        return $this->doGet($key, null, 'object', $default);
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value, int|\DateInterval|null $lifetime = null): void
    {
        $this->doSet($key, null, $value, $lifetime);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): void
    {
        $this->redis->unlink($this->validateKey($key));
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return $this->redis->exists($this->validateKey($key)) === 1;
    }

    /**
     * @inheritDoc
     */
    public function getItem(string $key, string $memberKey, mixed $default = null): mixed
    {
        return $this->doGet($key, $memberKey, 'mixed', $default);
    }

    /**
     * @inheritDoc
     */
    public function getItemBool(string $key, string $memberKey, ?bool $default = null): bool
    {
        return (bool)$this->doGet($key, $memberKey, 'bool', $default);
    }

    /**
     * @inheritDoc
     */
    public function getItemInt(string $key, string $memberKey, ?int $default = null): int
    {
        return (int)$this->doGet($key, $memberKey, 'int', $default);
    }

    /**
     * @inheritDoc
     */
    public function getItemFloat(string $key, string $memberKey, ?float $default = null): float
    {
        return (float)$this->doGet($key, $memberKey, 'float', $default);
    }

    /**
     * @inheritDoc
     */
    public function getItemString(string $key, string $memberKey, ?string $default = null): string
    {
        return (string)$this->doGet($key, $memberKey, 'string', $default);
    }

    /**
     * @inheritDoc
     */
    public function getItemArray(string $key, string $memberKey, ?array $default = null): array
    {
        return $this->doGet($key, $memberKey, 'array', $default);
    }

    /**
     * @inheritDoc
     */
    public function getItemObject(string $key, string $memberKey, object|string|null $default = null): mixed
    {
        return $this->doGet($key, $memberKey, 'object', $default);
    }

    public function getItemAll(string $key, ?array $default = null): array
    {
        if (!$this->has($key)) {
            if ($default === null) {
                throw NotFoundException::create($key);
            }
            return $default;
        }
        return $this->redis->hGetAll($this->validateKey($key));
    }

    /**
     * @inheritDoc
     */
    public function setItem(string $key, string $memberKey, mixed $value, \DateInterval|int|null $lifetime = null): void
    {
        $this->doSet($key, $memberKey, $value, $lifetime);
    }

    /**
     * @inheritDoc
     */
    public function deleteItem($key, $memberKey): bool
    {
        return $this->redis->hDel($this->validateKey($key), $this->validateKey($memberKey, false));
    }

    /**
     * @inheritDoc
     */
    public function hasItem($key, $memberKey): bool
    {
        return $this->redis->hExists($this->validateKey($key), $this->validateKey($memberKey, false));
    }

    /**
     * @inheritDoc
     */
    public function getLifeTime(string $key): int
    {
        return $this->redis->ttl($this->validateKey($key));
    }

    /**
     * @inheritDoc
     */
    public function setLifeTime(string $key, int|\DateInterval|null $lifetime = null): bool
    {
        return $this->redis->expire($this->validateKey($key), $this->validateLifeTime($lifetime));
    }

    /**
     * @inheritDoc
     */
    public function getKeys(?string $pattern = null): array
    {
        return $this->redis->keys($pattern ?? '*');
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $keys = $this->redis->keys($this->namespace . '*');
        $this->redis->unlink($keys);
        return count($this->redis->keys($this->namespace . '*')) === 0;
    }

    public function getClient(): \Redis|ClientInterface
    {
        return $this->redis;
    }

    /**
     * @throws \Mvc4us\Cache\Exception\NotFoundException
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     * @throws \RedisException
     */
    private function doGet(string $key, ?string $memberKey, string $type, mixed $default): mixed
    {
        $validKey = $this->validateKey($key);
        $validMemberKey = $memberKey ? $this->validateKey($memberKey, false) : null;

        if ($type === 'mixed' && $default !== null) {
            $type = gettype($default);
        }

        if (!$this->isValidDefault($type, $default)) {
            throw InvalidArgumentException::createInvalidDefaultType(gettype($default), $type);
        }

        if (!$this->has($key) || ($memberKey && !$this->hasItem($key, $memberKey))) {
            if ($default === null) {
                throw NotFoundException::create($key, $memberKey);
            }
            return $default;
        }

        if ($type === 'array') {
            if ($memberKey) {
                $value = $this->redis->hget($validKey, $validMemberKey);
            } else {
                $value = $this->redis->get($validKey);
            }
            if ($this->redis->getOption(\Redis::OPT_SERIALIZER) === 0) {
                $value = json_decode($value, true);
            }
            if (is_array($value)) {
                return $value;
            }
            if (is_array($default)) {
                return $default;
            }
            throw InvalidArgumentException::createInvalidType(gettype($value), $type);
        }

        if ($type === 'object') {
            if (is_object($default)) {
                $class = get_class($default);
            } elseif (is_string($default)) {
                $class = $default;
                $default = null;
            } else {
                throw InvalidArgumentException::createInvalidDefaultType('null', $type);
            }

            if ($memberKey) {
                $value = $this->redis->hget($validKey, $validMemberKey);
            } else {
                $value = $this->redis->get($validKey);
            }
            if ($this->redis->getOption(\Redis::OPT_SERIALIZER) === 0) {
                if ($this->serializer === null) {
                    throw new InvalidArgumentException('Serializer is not defined');
                }
                $value = $this->deserialize($value, $class);
            }
            if (is_object($value) && get_class($value) === $class) {
                return $value;
            }

            if (is_object($default)) {
                return $default;
            }
            throw InvalidArgumentException::createInvalidType(gettype($value), $type);
        }

        if ($memberKey) {
            $value = $this->redis->hget($validKey, $validMemberKey);
        } else {
            $value = $this->redis->get($validKey);
        }
        //if ($type !== gettype($value) && $type !== 'mixed') {
        //    throw InvalidArgumentException::createInvalidType(gettype($value), $type);
        //}
        return $value;
    }

    /**
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     * @throws \Mvc4us\Cache\Adapter\CacheException
     * @throws \Mvc4us\Cache\Adapter\InvalidArgumentException
     */
    private function doSet(string $key, ?string $memberKey, mixed $value, int|\DateInterval|null $lifetime): void
    {
        $validKey = $this->validateKey($key);
        $validMemberKey = $memberKey ? $this->validateKey($memberKey, false) : null;

        $lifetime = $this->validateLifeTime($lifetime);

        if (gettype($value) === 'array' && $this->redis->getOption(\Redis::OPT_SERIALIZER) === 0) {
            $value = json_encode($value);
        }

        if (gettype($value) === 'object' && $this->redis->getOption(\Redis::OPT_SERIALIZER) === 0) {
            if ($this->serializer === null) {
                throw new InvalidArgumentException('Serializer is not defined');
            }
            $value = $this->serialize($value);
        }

        if ($memberKey) {
            if ($this->redis->hSet($validKey, $validMemberKey, $value) === false) {
                throw CacheException::createFailedSet($key, $memberKey);
            }
            if ($lifetime === null) {
                return;
            }
            if ($this->redis->expire($validKey, $lifetime)) {
                return;
            }
            $this->redis->hDel($validKey, $validMemberKey);
        }

        if ($lifetime === null) {
            $ret = $this->redis->set($validKey, $value);
        } else {
            $ret = $this->redis->setEx($validKey, $lifetime, $value);
        }
        if (!$ret) {
            throw CacheException::createFailedSet($key);
        }
    }

    private function isValidDefault(string $type, mixed $default): bool
    {
        return ($type === 'mixed')
            || ($type !== 'object' && ($default === null || $type === gettype($default)))
            || ($type === 'object' && (gettype($default) === 'string' || gettype($default) === 'object'));
    }

    private function serialize(mixed $data): string
    {
        return $this->serializer->serialize(
            $data,
            'json',
            [
                JsonEncode::OPTIONS => JSON_HEX_TAG + JSON_HEX_AMP + JSON_HEX_APOS + JSON_HEX_QUOT,
                'circular_reference_limit' => 1,
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                    return null;
                },
                AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
                DateTimeNormalizer::TIMEZONE_KEY => 'UTC'
            ]
        );
    }

    private function deserialize(string $json, object|string $object): mixed
    {
        return $this->serializer->deserialize(
            $json,
            is_object($object) ? get_class($object) : $object,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => is_object($object) ? $object : null],
        );
    }
}

