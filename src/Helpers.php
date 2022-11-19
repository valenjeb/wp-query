<?php

declare(strict_types=1);

namespace Devly\WP\Query;

use Devly\Utils\StaticClass;

use function strtoupper;

class Helpers
{
    use StaticClass;

    public static function parseCompareOperator(string $operator): string
    {
        $operator = strtoupper($operator);

        switch ($operator) {
            case '!IN':
                $operator = 'NOT IN';
                break;
            case '!LIKE':
                $operator = 'NOT LIKE';
                break;
            case '!BETWEEN':
                $operator = 'NOT BETWEEN';
                break;
            case '!EXISTS':
                $operator = 'NOT EXISTS';
                break;
            case '!REGEXP':
                $operator = 'NOT REGEXP';
        }

        return strtoupper($operator);
    }
}
