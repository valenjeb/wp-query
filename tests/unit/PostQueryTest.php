<?php

declare(strict_types=1);

namespace Devly\WP\Query\Tests;

use Devly\WP\Query\PostQuery;
use Devly\WP\Query\TaxQuery;
use PHPUnit\Framework\TestCase;

class PostQueryTest extends TestCase
{
    protected PostQuery $builder;

    protected function setUp(): void
    {
        $this->builder = PostQuery::create();
    }

    public function testWhereAuthor(): void
    {
        $this->builder
            ->whereAuthor(123)
            ->whereAuthorName('rami')
            ->whereAuthorIn([2, 6])
            ->whereAuthorNotIn([1, 3]);

        $expected = [
            'author' => 123,
            'author_name' => 'rami',
            'author__in' => [2,6],
            'author__not_in' => [1,3],
        ];

        $this->assertEquals($expected, $this->builder->getQueryArgs());
    }

    public function testExcludeAuthor(): void
    {
        $this->builder->whereAuthor(-12);
        $this->builder->excludeAuthors(12);

        $this->assertEquals([
            'author' => -12,
            'author__not_in' => [12],
        ], $this->builder->getQueryArgs());
    }

    public function testWherePostType(): void
    {
        $this->builder->wherePostType('post');

        $this->assertEquals(['post_type' => 'post'], $this->builder->getQueryArgs());
    }

    public function testWherePostParameters(): void
    {
        $this->builder->wherePostID(1);
        $this->builder->wherePostIn(1, 2);
        $this->builder->wherePostNotIn(3, 4);
        $this->builder->whereName('hello-world');
        $this->builder->wherePostNameIn('foo', 'bar');
        $this->builder->whereParent(0);
        $this->builder->whereParentIn(5, 6);
        $this->builder->whereParentNotIn(7, 8);

        $this->assertEquals([
            'p' => 1,
            'post__in' => [1, 2],
            'post__not_in' => [3, 4],
            'post_name__in' => ['foo', 'bar'],
            'name' => 'hello-world',
            'post_parent' => 0,
            'post_parent__in' => [5, 6],
            'post_parent__not_in' => [7, 8],
        ], $this->builder->getQueryArgs());
    }

    public function testWherePageParameters(): void
    {
        $this->builder->wherePageID(10);
        $this->builder->wherePageName('sample-page');

        $this->assertEquals([
            'pagename' => 'sample-page',
            'page_id' => 10,
        ], $this->builder->getQueryArgs());
    }

    public function testWhereCategory(): void
    {
        $this->builder
            ->whereCategoryID(4)
            ->whereCategory('staff')
            ->whereCategoryIn(4)
            ->whereCategoryNotIn([1, 3])
            ->whereCategoryAnd([1, 3]);

        $expected = [
            'cat' => 4,
            'category_name' => 'staff',
            'category__in' => [4],
            'category__not_in' => [1,3],
            'category__and' => [1,3],
        ];

        $this->assertEquals($expected, $this->builder->getQueryArgs());
    }

    public function testWhereTag(): void
    {
        $this->builder->whereTagID(13);
        $this->builder->whereTag('cooking');
        $this->builder->whereTagAnd(37, 47);
        $this->builder->whereTagIn(37, 47);
        $this->builder->excludeByTagID(37, 47);
        $this->builder->whereTagSlugAnd('cooking', 'baking');
        $this->builder->whereTagSlugIn('cooking', 'baking');

        $this->assertEquals([
            'tag' => 'cooking',
            'tag_id' => 13,
            'tag__and' => [37, 47],
            'tag__in' => [37, 47],
            'tag__not_in' => [37, 47],
            'tag_slug__and' => ['cooking', 'baking'],
            'tag_slug__in' => ['cooking', 'baking'],
        ], $this->builder->getQueryArgs());
    }

    public function testSearchParameter(): void
    {
        $this->builder->search('keyword');

        $this->assertEquals(['s' => 'keyword'], $this->builder->getQueryArgs());
    }

    public function testWherePassword(): void
    {
        $this->builder->wherePassword('secret');

        $this->assertEquals(['post_password' => 'secret'], $this->builder->getQueryArgs());
    }

    public function testWherePasswordWithBoolean(): void
    {
        $this->builder->wherePassword(true);

        $this->assertEquals(['has_password' => true], $this->builder->getQueryArgs());
    }

    public function testWhereHasPassword(): void
    {
        $this->builder->whereHasPassword();

        $this->assertEquals(['has_password' => true], $this->builder->getQueryArgs());
    }

    public function testWherePostStatus(): void
    {
        $this->builder->whereStatus('draft');

        $this->assertEquals(['post_status' => 'draft'], $this->builder->getQueryArgs());

        $this->builder->whereStatus('draft', 'pending');

        $this->assertEquals(['post_status' => ['draft', 'pending']], $this->builder->getQueryArgs());
    }

    public function testWherePostStatusPublish(): void
    {
        $this->builder->whereIsPublished();

        $this->assertEquals(['post_status' => 'publish'], $this->builder->getQueryArgs());
    }

    public function testWherePostStatusPending(): void
    {
        $this->builder->whereIsPending();

        $this->assertEquals(['post_status' => 'pending'], $this->builder->getQueryArgs());
    }

    public function testWherePostStatusDraft(): void
    {
        $this->builder->whereIsDraft();

        $this->assertEquals(['post_status' => 'draft'], $this->builder->getQueryArgs());
    }

    public function testWhereCommentCount(): void
    {
        $this->builder->whereCommentCount(10);

        $this->assertEquals(['comment_count' => 10], $this->builder->getQueryArgs());
    }

    public function testWhereCommentCountWithCompareValue(): void
    {
        $this->builder->whereCommentCount(10, '>=');

        $this->assertEquals([
            'comment_count' => [
                'value' => 10,
                'compare' => '>=',
            ],
        ], $this->builder->getQueryArgs());
    }

    public function testSimpleTaxQuery(): void
    {
        $this->builder->whereTax('people', 'slug', 'bob');

        $this->assertEquals([
            'tax_query' => [
                [
                    'taxonomy' => 'people',
                    'field' => 'slug',
                    'terms' => 'bob',
                    'include_children' => true,
                    'operator' => 'IN',
                ],
            ],
        ], $this->builder->getQueryArgs());
    }

    public function testTaxQueryWithAndRelation(): void
    {
        $this->builder->whereTax('movie_genre', 'slug', ['action', 'comedy']);
        $this->builder->andWhereTax('actor', 'term_id', [103, 115, 206], '!in');

        $this->assertEquals([
            'tax_query' => [
                'relation' => 'AND',
                [
                    'taxonomy' => 'movie_genre',
                    'field'    => 'slug',
                    'terms'    => ['action', 'comedy'],
                    'include_children' => true,
                    'operator' => 'IN',
                ],
                [
                    'taxonomy' => 'actor',
                    'field' => 'term_id',
                    'terms' => [103, 115, 206],
                    'include_children' => true,
                    'operator' => 'NOT IN',
                ],
            ],
        ], $this->builder->getQueryArgs());
    }

    public function testTaxQueryWithOrRelation(): void
    {
        $this->builder->whereTax('category', 'slug', ['quotes']);
        $this->builder->orWhereTax('post_format', 'slug', ['post-format-quote']);

        $this->assertEquals([
            'tax_query' => [
                'relation' => 'OR',
                [
                    'taxonomy' => 'category',
                    'field' => 'slug',
                    'terms' => ['quotes'],
                    'include_children' => true,
                    'operator' => 'IN',
                ],
                [
                    'taxonomy' => 'post_format',
                    'field' => 'slug',
                    'terms' => ['post-format-quote'],
                    'include_children' => true,
                    'operator' => 'IN',
                ],
            ],
        ], $this->builder->getQueryArgs());
    }

    public function testNestedTaxQuery(): void
    {
        $this->builder->wherePostType('post');
        $this->builder->whereTax('category', 'slug', ['quotes']);
        $this->builder->orWhereTax(static function (TaxQuery $query): void {
            $query->where('post_format', 'slug', ['post-format-quote']);
            $query->andWhere('category', 'slug', ['wisdom'], '!exists');
        });

        $this->assertEquals([
            'post_type' => 'post',
            'tax_query' => [
                'relation' => 'OR',
                [
                    'taxonomy' => 'category',
                    'field' => 'slug',
                    'terms' => ['quotes'],
                    'include_children' => true,
                    'operator' => 'IN',
                ],
                [
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'post_format',
                        'field'    => 'slug',
                        'terms'    => ['post-format-quote'],
                        'include_children' => true,
                        'operator' => 'IN',
                    ],
                    [
                        'taxonomy' => 'category',
                        'field'    => 'slug',
                        'terms'    => ['wisdom'],
                        'include_children' => true,
                        'operator' => 'NOT EXISTS',
                    ],
                ],
            ],
        ], $this->builder->getQueryArgs());
    }
}
