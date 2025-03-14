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
    protected ?int $defaultLifetime = null;

    /**
     * @var int|null The maximum length to enforce for keys or null when no limit applies.
     */
    protected ?int $maxIdLength = null;

    /**
     * @internal
     */
    protected const NS_SEPARATOR = ':';

    /**
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     */
    protected function __construct(string $namespace = '', \DateInterval|int|null $defaultLifetime = null)
    {
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
    public function validateKey(string $key, bool $withNamespace = true): string
    {
        if ('' === $key) {
            throw new InvalidArgumentException('Cache key length must be greater than zero.');
        }
        if (strlen(static::RESERVED_CHARACTERS) && strpbrk($key, static::RESERVED_CHARACTERS) !== false) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Cache key/namespace "%s" contains reserved characters "%s".',
                    $key,
                    static::RESERVED_CHARACTERS
                )
            );
        }
        if ($withNamespace) {
            $key = $this->namespace . static::NS_SEPARATOR . $key;
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
     * @inheritDoc
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @inheritDoc
     */
    public function setNamespace(string $namespace): void
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
        $this->namespace = $namespace === '' ? '' : $this->validateKey($namespace, false);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultLifetime(): ?int
    {
        return $this->defaultLifetime;
    }

    /**
     * @inheritDoc
     */
    public function setDefaultLifetime(\DateInterval|int|null $defaultLifetime = null): void
    {
        $this->defaultLifetime = $this->validateLifeTime($defaultLifetime);
    }
}
