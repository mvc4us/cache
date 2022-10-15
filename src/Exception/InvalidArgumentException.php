<?php
declare(strict_types=1);

namespace Mvc4us\Cache\Exception;

/**
 * Exception interface for invalid cache arguments.
 * When an invalid argument is passed it must throw an exception which implements
 * this interface
 */
interface InvalidArgumentException extends CacheException
{
}
