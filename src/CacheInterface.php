<?php

declare(strict_types=1);

namespace Mvc4us\Cache;

/**
 * @author erdem
 */
interface CacheInterface
{
    /**
     * Fetches an item from the cache.
     *
     * @param string $key     The key of the item to retrieve from the cache
     * @param mixed  $default Default variable in case item is not found
     *
     * @return mixed                                            Returns the value for the given key/item
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException When the $key is not valid or when the type of item to
     *                                                          be returned does not match with the type of $default if
     *                                                          provided
     * @throws \Mvc4us\Cache\Exception\NotFoundException        When item is not found and $default is not provided
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * See {@link \Mvc4us\Cache\CacheInterface::get() CacheInterface::get()}
     *
     * @param string    $key
     * @param bool|null $default
     *
     * @return bool
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     * @throws \Mvc4us\Cache\Exception\NotFoundException
     */
    public function getBool(string $key, ?bool $default = null): bool;

    /**
     * See {@link \Mvc4us\Cache\CacheInterface::get() CacheInterface::get()}
     *
     * @param string   $key
     * @param int|null $default
     *
     * @return int
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     * @throws \Mvc4us\Cache\Exception\NotFoundException
     */
    public function getInt(string $key, ?int $default = null): int;

    /**
     * See {@link \Mvc4us\Cache\CacheInterface::get() CacheInterface::get()}
     *
     * @param string     $key
     * @param float|null $default
     *
     * @return float
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     * @throws \Mvc4us\Cache\Exception\NotFoundException
     */
    public function getFloat(string $key, ?float $default = null): float;

    /**
     * See {@link \Mvc4us\Cache\CacheInterface::get() CacheInterface::get()}
     *
     * @param string      $key
     * @param string|null $default
     *
     * @return string
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     * @throws \Mvc4us\Cache\Exception\NotFoundException
     */
    public function getString(string $key, ?string $default = null): string;

    /**
     * See {@link \Mvc4us\Cache\CacheInterface::get() CacheInterface::get()}
     *
     * @param string     $key
     * @param array|null $default
     *
     * @return array
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     * @throws \Mvc4us\Cache\Exception\NotFoundException
     */
    public function getArray(string $key, ?array $default = null): array;

    /**
     * Fetches a class from cache.
     * If $default is a Fully Qualified Class Name then it will be the type and the default will be null.
     * If $default is an instance of a class then it will be used as a default value and FQCN will be type.
     *
     * @param string             $key
     * @param object|string|null $default
     *
     * @return object
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     * @throws \Mvc4us\Cache\Exception\NotFoundException
     */
    public function getObject(string $key, object|string|null $default): mixed;

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                 $key      The key of the item to store
     * @param mixed                  $value    The value of the item to store, must be serializable
     * @param int|\DateInterval|null $lifetime Optional. If no value is sent and the driver supports then the library
     *                                         may set a default value for it or let the driver take care of that
     *
     * @return void
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException When the $key is not valid
     * @throws \Mvc4us\Cache\Exception\CacheException           When the item can not be stored into cache
     */
    public function set(string $key, mixed $value, int|\DateInterval|null $lifetime = null): void;

    /**
     * Deletes an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return void
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException When the $key is not valid
     * @throws \Mvc4us\Cache\Exception\CacheException           When the item can not be deleted from cache
     */
    public function delete(string $key): void;

    /**
     * Determines whether an item is present in the cache.
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The unique cache key of the item.
     *
     * @return bool
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException MUST be thrown if the key is not a legal value.
     */
    public function has(string $key): bool;

    /**
     * If the adapter supports fetches a value from a table like item with a member key.
     *
     * @param string $key       The key of the table in the cache
     * @param string $memberKey The key of the item to retrieve from the table
     * @param mixed  $default   Default variable in case item is not found
     *
     * @return mixed Returns the value for the given key/item
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException When the $key is not valid or when the type of item to
     *                                                          be returned does not match with the type of $default if
     *                                                          provided
     * @throws \Mvc4us\Cache\Exception\NotFoundException        When item is not found and $default is not provided
     */
    public function getItem(string $key, string $memberKey, mixed $default = null): mixed;

    /**
     * See {@link \Mvc4us\Cache\CacheInterface::getItem() CacheInterface::getItem()}
     *
     * @param string    $key
     * @param bool|null $default
     * @param string    $memberKey
     *
     * @return bool
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     * @throws \Mvc4us\Cache\Exception\NotFoundException
     */
    public function getItemBool(string $key, string $memberKey, ?bool $default = null): bool;

    /**
     * See {@link \Mvc4us\Cache\CacheInterface::getItem() CacheInterface::getItem()}
     *
     * @param string   $key
     * @param string   $memberKey
     * @param int|null $default
     *
     * @return int
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     * @throws \Mvc4us\Cache\Exception\NotFoundException
     */
    public function getItemInt(string $key, string $memberKey, ?int $default = null): int;

    /**
     * See {@link \Mvc4us\Cache\CacheInterface::getItem() CacheInterface::getItem()}
     *
     * @param string     $key
     * @param string     $memberKey
     * @param float|null $default
     *
     * @return float
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     * @throws \Mvc4us\Cache\Exception\NotFoundException
     */
    public function getItemFloat(string $key, string $memberKey, ?float $default = null): float;

    /**
     * See {@link \Mvc4us\Cache\CacheInterface::getItem() CacheInterface::getItem()}
     *
     * @param string      $key
     * @param string      $memberKey
     * @param string|null $default
     *
     * @return string
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     * @throws \Mvc4us\Cache\Exception\NotFoundException
     */
    public function getItemString(string $key, string $memberKey, ?string $default = null): string;

    /**
     * See {@link \Mvc4us\Cache\CacheInterface::getItem() CacheInterface::getItem()}
     *
     * @param string     $key
     * @param string     $memberKey
     * @param array|null $default
     *
     * @return array
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     * @throws \Mvc4us\Cache\Exception\NotFoundException
     */
    public function getItemArray(string $key, string $memberKey, ?array $default = null): array;

    /**
     * Fetches a class from cache.
     * If $default is a FQCN then this will be the type and the default will be null.
     * If $default is an instance of the class then will be used as a type and a default value.
     * See {@link \Mvc4us\Cache\CacheInterface::getItem() CacheInterface::getItem()}
     *
     * @param string             $key
     * @param string             $memberKey
     * @param object|string|null $default
     *
     * @return object
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     * @throws \Mvc4us\Cache\Exception\NotFoundException
     */
    public function getItemObject(string $key, string $memberKey, object|string|null $default = null): mixed;

    /**
     * If the adapter supports fetches the whole table as an array.
     *
     * @param string     $key     The key of the table in the cache
     * @param array|null $default Default variable in case item is not found
     *
     * @return array
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException When the $key is not valid or when the type of item to
     *                                                          be returned does not match with the type of $default if
     *                                                          provided
     * @throws \Mvc4us\Cache\Exception\NotFoundException        When item is not found and $default is not provided
     */
    public function getItemAll(string $key, ?array $default = null): array;

    /**
     * If the adapter supports persists data into a table in the cache, uniquely referenced by a key and a member key
     * with an optional expiration TTL time.
     *
     * @param string                 $key       The key of a table in the cache.
     * @param string                 $memberKey The unique key of an item in the table.
     * @param mixed                  $value     The value of the item to store, must be serializable.
     * @param int|\DateInterval|null $lifetime  Optional. The TTL value of table/item. If no value is sent and the
     *                                          driver supports TTL then the library may set a default value for it or
     *                                          let the driver take care of that.
     *
     * @return void
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException When the $key/$memberKey is not valid
     * @throws \Mvc4us\Cache\Exception\CacheException           When the item can not be stored into cache/table
     */
    public function setItem(string $key, string $memberKey, mixed $value, \DateInterval|int|null $lifetime): void;

    /**
     * Deletes an item from a hash table from the cache by its unique key.
     *
     * @param string $key       The unique key of this table in the cache.
     * @param string $memberKey The unique key of the item in this table.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException When the $key/$memberKey is not valid
     * @throws \Mvc4us\Cache\Exception\CacheException           When the item can not be deleted from cache
     */
    public function deleteItem(string $key, string $memberKey): bool;

    /**
     * Determines whether an item in a hash table is present in the cache.
     * NOTE: It is recommended that hasItem() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key       The unique key of this table in the cache.
     * @param string $memberKey The unique key of the item in this table.
     *
     * @return bool
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException MUST be thrown if the $key and/or $memberKey string is
     *                          not a legal value.
     */
    public function hasItem(string $key, string $memberKey): bool;

    /**
     * Gets remaining lifetime of an item in seconds.
     *
     * @param string $key The unique cache key of the item.
     *
     * @return int
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException MUST be thrown if the key is not a legal value.
     */
    public function getLifeTime(string $key): int;

    /**
     * Sets or resets lifetime of an item.
     *
     * @param string                 $key      The key of the item to store.
     * @param int|\DateInterval|null $lifetime If no value is sent and the driver supports then the library may set a
     *                                         default value for it or let the driver take care of that.
     *
     * @return bool                            True on success and false on failure.
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException MUST be thrown if the key is not a legal value.
     */
    public function setLifeTime(string $key, int|\DateInterval|null $lifetime = null): bool;

    /**
     * Return all keys matching pattern (like in Redis KEYS).
     *
     * @param string|null $pattern
     *
     * @return string[]
     */
    public function getKeys(?string $pattern = null): array;

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear(): bool;

    /**
     * Returns key prefix (namespace) of this instance
     *
     * @return string
     */
    public function getNamespace(): string;

    /**
     * Sets key prefix (namespace) of this instance
     *
     * @param string $namespace
     *
     * @return void
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     */
    public function setNamespace(string $namespace): void;

    /**
     * Gets default lifetime in seconds for items.
     *
     * @return int|null
     */
    public function getDefaultLifetime(): ?int;

    /**
     * Sets default lifetime for items.
     *
     * @param \DateInterval|int|null $defaultLifetime
     *
     * @return void
     */
    public function setDefaultLifetime(\DateInterval|int|null $defaultLifetime): void;
}

