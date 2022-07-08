<?php
declare(strict_types=1);

namespace Mvc4us\Cache\Redis;

use Mvc4us\Cache\Adapter\CacheException;
use Mvc4us\Cache\Adapter\InvalidArgumentException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * This Code is copied and modified from Symfony Cache Component
 *
 * @link https://github.com/symfony/cache/blob/6.0/Traits/RedisTrait.php
 */
trait RedisTrait
{
    private \Redis|\Predis\ClientInterface $redis;

    private ?SerializerInterface $serializer = null;

    private static array $defaultConnectionOptions = [
        'class' => null,
        'persistent' => 0,
        'persistent_id' => null,
        'timeout' => 30,
        'read_timeout' => 0,
        'retry_interval' => 0,
        'tcp_keepalive' => 0,
        'lazy' => null,
        'redis_cluster' => false,
        'redis_sentinel' => null,
        'dbindex' => 0,
        'failover' => 'none',
        'ssl' => null, // see https://php.net/context.ssl
        'serializer' => 0,
    ];

    /**
     * @throws \Mvc4us\Cache\Exception\InvalidArgumentException
     * @throws \Mvc4us\Cache\Exception\CacheException
     */
    private function initialize(string $dsn, array $options): void
    {
        $this->maxIdLength = 1024;

        if (str_starts_with($dsn, 'redis:')) {
            $scheme = 'redis';
        } elseif (str_starts_with($dsn, 'rediss:')) {
            $scheme = 'rediss';
        } else {
            throw new InvalidArgumentException(
                sprintf('Invalid Redis DSN: "%s" does not start with "redis:" or "rediss:".', $dsn)
            );
        }

        if (!extension_loaded('redis') && !class_exists(\Predis\Client::class)) {
            throw new CacheException(
                sprintf('Cannot find the "redis" extension nor the "predis/predis" package: "%s".', $dsn)
            );
        }

        $params = preg_replace_callback(
            '#^' . $scheme . ':(//)?(?:(?:(?<user>[^:@]*+):)?(?<password>[^@]*+)@)?#',
            function ($m) use (&$auth) {
                if (isset($m['password'])) {
                    if (\in_array($m['user'], ['', 'default'], true)) {
                        $auth = $m['password'];
                    } else {
                        $auth = [$m['user'], $m['password']];
                    }

                    if ('' === $auth) {
                        $auth = null;
                    }
                }

                return 'file:' . ($m[1] ?? '');
            },
            $dsn
        );

        $params = parse_url($params);
        if ($params === false) {
            throw new InvalidArgumentException(sprintf('Invalid Redis DSN: "%s".', $dsn));
        }

        $query = $hosts = [];

        $tls = $scheme === 'rediss';
        $tcpScheme = $tls ? 'tls' : 'tcp';

        if (isset($params['query'])) {
            parse_str($params['query'], $query);

            if (isset($query['host'])) {
                if (!is_array($hosts = $query['host'])) {
                    throw new InvalidArgumentException(sprintf('Invalid Redis DSN: "%s".', $dsn));
                }
                foreach ($hosts as $host => $parameters) {
                    if (is_string($parameters)) {
                        parse_str($parameters, $tmpArr);
                        $parameters = $tmpArr;
                    }
                    $i = strrpos($host, ':');
                    if ($i === false) {
                        $hosts[$host] = ['scheme' => $tcpScheme, 'host' => $host, 'port' => 6379] + $parameters;
                    } elseif ($port = (int)substr($host, 1 + $i)) {
                        $hosts[$host] = [
                                'scheme' => $tcpScheme,
                                'host' => substr($host, 0, $i),
                                'port' => $port
                            ] + $parameters;
                    } else {
                        $hosts[$host] = ['scheme' => 'unix', 'path' => substr($host, 0, $i)] + $parameters;
                    }
                }
                $hosts = array_values($hosts);
            }
        }

        if (isset($params['host']) || isset($params['path'])) {
            if (!isset($params['dbindex']) && isset($params['path'])) {
                if (preg_match('#/(\d+)$#', $params['path'], $m)) {
                    $params['dbindex'] = (int)$m[1];
                    $params['path'] = substr($params['path'], 0, -strlen($m[0]));
                } elseif (isset($params['host'])) {
                    throw new InvalidArgumentException(
                        sprintf('Invalid Redis DSN: "%s", the "dbindex" parameter must be a number.', $dsn)
                    );
                }
            }

            if (isset($params['host'])) {
                array_unshift(
                    $hosts,
                    ['scheme' => $tcpScheme, 'host' => $params['host'], 'port' => $params['port'] ?? 6379]
                );
            } else {
                array_unshift($hosts, ['scheme' => 'unix', 'path' => $params['path']]);
            }
        }

        if (!$hosts) {
            throw new InvalidArgumentException(sprintf('Invalid Redis DSN: "%s".', $dsn));
        }

        $params += $query + $options + self::$defaultConnectionOptions;

        if (null === $params['class'] && \extension_loaded('redis')) {
            $class = \Redis::class;
        } else {
            $class = $params['class'] ?? \Predis\Client::class;
        }

        if (is_a($class, \Redis::class, true)) {
            $connect = $params['persistent'] || $params['persistent_id'] ? 'pconnect' : 'connect';
            $redis = new $class();
            $that = $this;
            $initializer = static function ($redis) use ($that, $connect, $params, $dsn, $auth, $hosts, $tls) {
                $host = $hosts[0]['host'] ?? $hosts[0]['path'];
                $port = $hosts[0]['port'] ?? 0;

                if (isset($hosts[0]['host']) && $tls) {
                    $host = 'tls://' . $host;
                }

                try {
                    @$redis->{$connect}(
                        $host,
                        $port,
                        $params['timeout'],
                        (string)$params['persistent_id'],
                        $params['retry_interval'],
                        $params['read_timeout'],
                        ...
                        \defined('Redis::SCAN_PREFIX') ? [['stream' => $params['ssl'] ?? null]] : []
                    );

                    set_error_handler(function ($type, $msg) use (&$error) {
                        $error = $msg;
                    });
                    try {
                        $isConnected = $redis->isConnected();
                    } finally {
                        restore_error_handler();
                    }
                    if (!$isConnected) {
                        $error = preg_match('/^Redis::p?connect\(\): (.*)/', $error, $error) ? sprintf(
                            ' (%s)',
                            $error[1]
                        ) : '';
                        throw new InvalidArgumentException(
                            sprintf('Redis connection "%s" failed: ', $dsn) . $error . '.'
                        );
                    }

                    if ((null !== $auth && !$redis->auth($auth))
                        || ($params['dbindex'] && !$redis->select($params['dbindex']))
                    ) {
                        $e = preg_replace('/^ERR /', '', $redis->getLastError());
                        throw new InvalidArgumentException(sprintf('Redis connection "%s" failed: ', $dsn) . $e . '.');
                    }

                    if (0 < $params['tcp_keepalive'] && \defined('Redis::OPT_TCP_KEEPALIVE')) {
                        $redis->setOption(\Redis::OPT_TCP_KEEPALIVE, $params['tcp_keepalive']);
                    }

                    if ($params['serializer'] instanceof SerializerInterface) {
                        $that->serializer = $params['serializer'];
                    } elseif (is_int($params['serializer'])) {
                        $redis->setOption(\Redis::OPT_SERIALIZER, $params['serializer']);
                    }
                } catch (\RedisException $e) {
                    throw new InvalidArgumentException(
                        sprintf('Redis connection "%s" failed: ', $dsn) . $e->getMessage()
                    );
                }

                return true;
            };

            $initializer($redis);
        } elseif (is_a($class, \Predis\ClientInterface::class, true)) {
            $params += ['parameters' => []];
            $params['parameters'] += [
                'persistent' => $params['persistent'],
                'timeout' => $params['timeout'],
                'read_write_timeout' => $params['read_timeout'],
                'tcp_nodelay' => true,
            ];
            if ($params['dbindex']) {
                $params['parameters']['database'] = $params['dbindex'];
            }
            if (null !== $auth) {
                $params['parameters']['password'] = $auth;
            }
            $hosts = $hosts[0];
            $params['exceptions'] = false;

            $redis = new $class(
                $hosts,
                array_diff_key($params, array_diff_key(self::$defaultConnectionOptions, ['ssl' => null]))
            );
        } elseif (class_exists($class, false)) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" is not a subclass of "Redis", "RedisArray", "RedisCluster" nor "Predis\ClientInterface".',
                    $class
                )
            );
        } else {
            throw new InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $this->redis = $redis;
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->serializer = $serializer;
        //$this->redis->setOption(\Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
    }
}
