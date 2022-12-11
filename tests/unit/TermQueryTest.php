<?php

declare(strict_types=1);

namespace Devly\WP\Query\Tests\Unit;

use Devly\WP\Query\TermQuery;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TermQueryTest extends TestCase
{
    public function testBuildQuery(): void
    {
        $query = TermQuery::create()
            ->whereTaxonomy('tax_name')
            ->whereObjectIDs(1, 2)
            ->orderByID('desc')
            ->hideEmpty()
            ->include(3, 4)
            ->exclude(5, 6)
            ->excludeTree(7, 8)
            ->limit(3)
            ->skip(4)
            ->fields('all')
            ->whereName('name')
            ->whereSlug('slug')
            ->whereTermTaxonomyID(1)
            ->whereHierarchical()
            ->search('term')
            ->whereNameLike('genre')
            ->whereDescriptionLike('description')
            ->padCounts()
            ->whereChildOf(1)
            ->whereParent(0)
            ->whereChildless()
            ->cacheDomain('domain')
            ->updateTermMetaCache(false)
            ->whereMetaQuery('color', 'orange')
            ->whereMetaKey('key_name')
            ->whereMetaValue('value')
            ->metaType('numeric')
            ->metaCompare('!between');

        $this->assertEquals([
            'taxonomy'               => 'tax_name',
            'object_ids'             => [1, 2],
            'orderby'                => 'id',
            'order'                  => 'DESC',
            'hide_empty'             => true,
            'include'                => [3, 4],
            'exclude'                => [5, 6],
            'exclude_tree'           => [7, 8],
            'number'                 => 3,
            'offset'                 => 4,
            'fields'                 => 'all',
            'name'                   => 'name',
            'slug'                   => 'slug',
            'term_taxonomy_id'       => 1,
            'hierarchical'           => true,
            'search'                 => 'term',
            'name__like'             => 'genre',
            'description__like'      => 'description',
            'pad_counts'             => true,
            'child_of'               => 1,
            'parent'                 => 0,
            'childless'              => true,
            'cache_domain'           => 'domain',
            'update_term_meta_cache' => false,
            'meta_query'             => [
                [
                    'key'     => 'color',
                    'value'   => 'orange',
                    'compare' => '=',
                    'type' => 'CHAR',
                ],
            ],
            'meta_key'               => 'key_name',
            'meta_value'             => 'value',
            'meta_type'              => 'numeric',
            'meta_compare'           => 'NOT BETWEEN',
        ], $query->getQueryArgs());
    }

    public function testThrowsInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TermQuery::create()->whereTaxonomy(['tax_name', 1])->getQueryArgs(); // @phpstan-ignore-line
    }
}
