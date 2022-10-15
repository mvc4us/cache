<?php
declare(strict_types=1);

namespace Mvc4us\Cache\Adapter;

use Mvc4us\Cache\Exception\CacheException as Mvc4usCacheException;

/**
 * @author erdem
 */
class CacheException extends \Exception implements Mvc4usCacheException
{
    public static function createFailedSet(string $key, ?string $memberKey = null): self
    {
        if ($memberKey) {
            return new self(sprintf("Failed to set item with key '%s' and member key '%s'", $key, $memberKey));
        }
        return new self(sprintf("Failed to set item with key '%s'", $key));
    }

    private static function createFailedDelete(string $key): self
    {
        return new self(sprintf("Failed to delete item with key '%s'", $key));
    }

    private static function createFailedSetItem(string $key, string $memberKey): self
    {
        return new self(sprintf("Failed to set table item with key '%s'", $key));
    }

    private static function createFailedDeleteItem(string $key, string $memberKey): self
    {
        return new self(sprintf("Failed to delete item with key '%s'", $key));
    }
}

