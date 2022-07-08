<?php

namespace Mvc4us\Cache\Adapter;

use Mvc4us\Cache\Exception\InvalidArgumentException as Mvc4usInvalidArgumentException;

/**
 * @author erdem
 */
class InvalidArgumentException extends \Exception implements Mvc4usInvalidArgumentException
{

    public static function createInvalidType(string $itemType, string $type): self
    {
        return new self(sprintf("Item type of '%s' is not matching with expected '%s'", $itemType, $type));
    }

    public static function createInvalidDefaultType(string $defaultType, string $type): self
    {
        return new self(sprintf("Given default type of '%s' is not matching with expected '%s'", $defaultType, $type));
    }

    public static function createFailedTypeCast(string $from, string $to): self
    {
        return new self(sprintf("Can not type cast from '%s' to '%s'", $from, $to));
    }
}

