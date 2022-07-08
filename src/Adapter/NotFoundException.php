<?php
declare(strict_types=1);

namespace Mvc4us\Cache\Adapter;

use Mvc4us\Cache\Exception\NotFoundException as Mvc4usNotFoundException;

class NotFoundException extends \Exception implements Mvc4usNotFoundException
{
    public static function create(string $key, ?string $memberKey = null): self
    {
        if ($memberKey) {
            return new self(sprintf("Item not found with key '%s' and member key '%s'", $key, $memberKey));
        }
        return new self(sprintf("Item not found with key '%s'", $key));
    }
}
