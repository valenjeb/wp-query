<?php

declare(strict_types=1);

namespace Devly\WP\Query\Tests\Unit;

use Devly\WP\Query\UserQuery;
use PHPUnit\Framework\TestCase;

class UserQueryTest extends TestCase
{
    protected UserQuery $builder;

    protected function setUp(): void
    {
        $this->builder = new UserQuery();
    }

    public function testUserRoleParameters(): void
    {
        $this->builder->whereRole('Administrator');
        $this->builder->whereRoleIn('Administrator', 'Subscriber');
        $this->builder->whereRoleNotIn('Subscriber');

        $this->assertEquals([
            'role' => 'Administrator',
            'role__in' => ['Administrator', 'Subscriber'],
            'role__not_in' => ['Subscriber'],
        ], $this->builder->getQueryArgs());
    }

    public function testIncludeAndExcludeParameters(): void
    {
        $this->builder->whereUserIn(1, 2, 3);
        $this->builder->whereUserNotIn(4, 5, 6);

        $this->assertEquals([
            'include' => [ 1, 2, 3 ],
            'exclude' => [ 4, 5, 6 ],
        ], $this->builder->getQueryArgs());
    }

    public function testWhereBlogID(): void
    {
        $this->builder->whereBlogID(1);

        $this->assertEquals(['blog_id' => 1], $this->builder->getQueryArgs());
    }

    public function testSearchParameters(): void
    {
        $this->builder->search('John', ['user_login', 'user_email']);

        $this->assertEquals([
            'search'         => 'John',
            'search_columns' => ['user_login', 'user_email'],
        ], $this->builder->getQueryArgs());
    }

    public function testPaginateResults(): void
    {
        $this->builder->paginate(10, 2);

        $this->assertEquals([
            'number'         => 10,
            'paged' => 2,
        ], $this->builder->getQueryArgs());
    }
}
