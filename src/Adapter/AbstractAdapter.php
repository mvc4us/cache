<?php

declare(strict_types=1);

namespace Mvc4us\Cache\Adapter;

use Mvc4us\Cache\CacheInterface;

abstract class AbstractAdapter implements CacheInterface
{

    /**
     * Reserved characters that cannot be used in a key.
     */
    public const RESERVED_CHARACTERS = '{}()/\@:';

    /**
     * @var string Optional item key prefix.
     */
    protected string $namespace = '';

    /**
     * @var int|null Default lifetime for items or null for persistent storage.
     */
    protected ?int $defaultLifetime;

    /**
     * @var int|null The maximum length to enforce for keys or null when no limit applies.
     */
    protected ?int $maxIdLength;

    /**
     * @internal
     */
    protected const NS_SEPARATOR = ':';

    /**
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     */
    protected function __construct(string $namespace = '', \DateInterval|int|null $defaultLifetime = null)
    {
        if ($this->maxIdLength !== null && \strlen($namespace) > $this->maxIdLength - 24) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Namespace must be %d chars max, %d given ("%s").',
                    $this->maxIdLength - 24,
                    \strlen($namespace),
                    $namespace
                )
            );
        }
        $this->setNamespace($namespace);
        $this->defaultLifetime = $this->validateLifeTime($defaultLifetime);
    }

    /**
     * Validate cache item key.
     *
     * @param string $key
     * @param bool $withNamespace
     * @return string
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     */
    protected function validateKey(string $key, bool $withNamespace = true): string
    {
        if ('' === $key) {
            throw new InvalidArgumentException('Cache key length must be greater than zero.');
        }
        if (strpbrk($key, self::RESERVED_CHARACTERS) !== false) {
            throw new InvalidArgumentException(
                \sprintf('Cache key "%s" contains reserved characters "%s".', $key, self::RESERVED_CHARACTERS)
            );
        }
        if ($withNamespace) {
            $key = $this->namespace . $key;
        }
        if ($this->maxIdLength !== null && \strlen($key) > $this->maxIdLength) {
            throw new InvalidArgumentException(
                \sprintf('Key must be %d chars max, %d given ("%s").', $this->maxIdLength, \strlen($key), $key)
            );
        }
        return $key;
    }

    /**
     * Validate lifeTime and return as seconds.
     *
     * @param \DateInterval|int|null $lifeTime
     * @return int|null
     */
    protected function validateLifeTime(\DateInterval|int|null $lifeTime): ?int
    {
        if ($lifeTime instanceof \DateInterval) {
            $lifeTime = $lifeTime->days * 86400 + $lifeTime->h * 3600 + $lifeTime->i * 60 + $lifeTime->s;
        } else {
            $lifeTime = $lifeTime ?? $this->defaultLifetime;
        }
        if ($lifeTime <= 0 || $lifeTime === null) {
            return null;
        }
        return $lifeTime;
    }

    /**
     * Returns key prefix (namespace) of this instance
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * Sets key prefix (namespace) of this instance
     *
     * @param string $namespace
     * @return void
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace === '' ? '' : $this->validateKey($namespace, false) . static::NS_SEPARATOR;
    }

    /**
     * Gets default lifetime in seconds for items.
     *
     * @return int|null
     */
    public function getDefaultLifetime(): ?int
    {
        return $this->defaultLifetime;
    }

    /**
     * Sets default lifetime for items.
     *
     * @param \DateInterval|int|null $defaultLifetime
     * @return void
     */
    public function setDefaultLifetime(\DateInterval|int|null $defaultLifetime): void
    {
        $this->defaultLifetime = $this->validateLifeTime($defaultLifetime);
    }
}
