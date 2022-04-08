<?php

declare(strict_types=1);

namespace Mvc4us\Cache;

/**
 * @author erdem
 */
interface CacheInterface
{
    /**
     * Fetches a value from the cache.
     *
     * @param string $key The unique key of this item in the cache.
     * @param mixed $var  Variable to be set with fetched value. Also acts as a default value if the key does not exist.
     * @return bool True on success and false on failure.
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException MUST be thrown if the key is not a legal value.
     */
    public function get(string $key, mixed &$var): bool;

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string $key                      The key of the item to store.
     * @param mixed $value                     The value of the item to store, must be serializable.
     * @param int|\DateInterval|null $lifetime Optional. If no value is sent and the driver supports then the library
     *                                         may set a default value for it or let the driver take care of that.
     * @return bool True on success and false on failure.
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException MUST be thrown if the key is not a legal value.
     */
    public function set(string $key, mixed $value, int|\DateInterval|null $lifetime = null): bool;

    /**
     * Deletes an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     * @return bool True if the item was successfully removed. False if there was an error.
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException MUST be thrown if the key is not a legal value.
     */
    public function delete(string $key): bool;

    /**
     * Determines whether an item is present in the cache.
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The unique cache key of the item.
     * @return bool
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException MUST be thrown if the key is not a legal value.
     */
    public function has(string $key): bool;

    /**
     * Fetches a value from a hash table from the cache.
     *
     * @param string $key       The unique key of a table in the cache.
     * @param string $memberKey The unique key of an item in the table.
     * @param mixed $var        Variable to be set with returned value. Also acts as a default value if the key does not
     *                          exist.
     * @return bool True on success and false on failure.
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException MUST be thrown if the $key and/or $memberKey string is
     *                          not a legal value.
     */
    public function getItem(string $key, string $memberKey, mixed &$var): bool;

    /**
     * Persists data into a hash table in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string $key                      The key of a table in the cache.
     * @param string $memberKey                The unique key of an item in the table.
     * @param mixed $value                     The value of the item to store, must be serializable.
     * @param int|\DateInterval|null $lifetime Optional. The TTL value of this item. If no value is sent and the driver
     *                                         supports TTL then the library may set a default value for it or let the
     *                                         driver take care of that.
     * @return bool True on success and false on failure.
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException MUST be thrown if the $key and/or $memberKey string is
     *                                         not a legal value.
     */
    public function setItem(string $key, string $memberKey, mixed $value, \DateInterval|int|null $lifetime): bool;

    /**
     * Deletes an item from a hash table from the cache by its unique key.
     *
     * @param string $key       The unique key of this table in the cache.
     * @param string $memberKey The unique key of the item in this table.
     * @return bool True if the item was successfully removed. False if there was an error.
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException MUST be thrown if the $key and/or $memberKey string is
     *                          not a legal value.
     */
    public function deleteItem(string $key, string $memberKey): bool;

    /**
     * Determines whether an item in a hash table is present in the cache.
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key       The unique key of this table in the cache.
     * @param string $memberKey The unique key of the item in this table.
     * @return bool
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException MUST be thrown if the $key and/or $memberKey string is
     *                          not a legal value.
     */
    public function hasItem(string $key, string $memberKey): bool;

    /**
     * Gets remaining lifetime of an item in seconds.
     *
     * @param string $key The unique cache key of the item.
     * @return int
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException MUST be thrown if the key is not a legal value.
     */
    public function getLifeTime(string $key): int;

    /**
     * Sets or resets lifetime of an item.
     *
     * @param string $key                      The key of the item to store.
     * @param int|\DateInterval|null $lifetime If no value is sent and the driver supports then the library may set a
     *                                         default value for it or let the driver take care of that.
     * @return bool True on success and false on failure.
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException MUST be thrown if the key is not a legal value.
     */
    public function setLifeTime(string $key, int|\DateInterval|null $lifetime = null): bool;

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear(): bool;

    /**
     * Returns found (return) status of last get() call.
     *
     * @return bool
     */
    public function found(): bool;

    /**
     * Returns not found (return) status of last get() call.
     *
     * @return bool
     */
    public function notFound(): bool;
}

