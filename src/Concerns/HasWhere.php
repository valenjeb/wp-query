<?php

declare(strict_types=1);

namespace Devly\WP\Query\Concerns;

use InvalidArgumentException;

use function func_num_args;
use function is_string;
use function sprintf;

trait HasWhere
{
    /**
     * @param string|mixed $operatorOrValue
     * @param mixed        $value
     *
     * @return static
     */
    protected function where(string $key, $operatorOrValue, $value = null): self
    {
        if (func_num_args() === 2) {
            $value    = $operatorOrValue;
            $operator = '=';
        } else {
            $operator = $operatorOrValue;
        }

        if (! is_string($operator)) {
            throw new InvalidArgumentException(sprintf('The #2 %s::where() method parameter must be a string.', static::class));
        }

        switch ($operator) {
            case 'in':
                return $this->whereIn($key, $value);

            case '!in':
            case 'not_in':
                return $this->whereNotIn($key, $value);

            case 'and':
            case '&&':
            case '&':
                return $this->whereAnd($key, $value);

            case '=':
            case '==':
            case 'equals':
                return $this->set($key, $value);

            default:
                throw new InvalidArgumentException(sprintf('Unsupported operator "%s"', $operator));
        }
    }

    /**
     * @param array<string|int> $values
     *
     * @return static
     */
    protected function whereIn(string $option, array $values): self
    {
        return $this->set($option . '__in', $values);
    }

    /**
     * @param array<string|int> $values
     *
     * @return static
     */
    protected function whereAnd(string $key, array $values): self
    {
        return $this->set($key . '__and', $values);
    }

    /**
     * @param array<string|int> $values
     *
     * @return static
     */
    protected function whereNotIn(string $key, array $values): self
    {
        return $this->set($key . '__not_in', $values);
    }
}
