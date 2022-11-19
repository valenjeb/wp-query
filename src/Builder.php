<?php

declare(strict_types=1);

namespace Devly\WP\Query;

use InvalidArgumentException;

use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;

abstract class Builder
{
    /** @var array<string, mixed> */
    protected array $query = [];

    /**
     * @param array<string, mixed>|self $query
     *
     * @throws InvalidArgumentException
     */
    public function __construct($query = [])
    {
        if ($query instanceof self) {
            $query = $query->getQueryArgs();
        }

        if (! is_array($query)) {
            throw new InvalidArgumentException(sprintf(
                'The %1$s constructor expects a list of key-value pairs or an instance of %1$s. Provided %2$s.',
                self::class,
                is_object($query) ? get_class($query) : gettype($query) // @phpstan-ignore-line
            ));
        }

        $this->set($query);
    }

    /**
     * @param array<string, mixed>|self $query
     *
     * @return static
     */
    public static function create($query = []): self
    {
        return new static($query); // @phpstan-ignore-line
    }

    /** @return array<string, mixed> */
    public function getQueryArgs(): array
    {
        return $this->query;
    }

    /**
     * @param string|array<string, mixed> $key
     * @param mixed                       $value
     *
     * @return static
     */
    public function set($key, $value = null): self
    {
        if (is_array($key)) {
            foreach ($key as $value) {
                $this->set($value, $value);
            }

            return $this;
        }

        if (! is_string($key)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid $key provided to %s::set() method. Must be a string or a key value pair list. Provide %s.',
                static::class,
                gettype($key)
            ));
        }

        $this->query[$key] = $value;

        return $this;
    }

    /**
     * @param mixed $default Default value to return.
     *
     * @return mixed
     */
    public function getKey(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }

    public function reset(): self
    {
        $this->query = [];

        return $this;
    }
}
