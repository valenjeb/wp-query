<?php

namespace Devly\WP\Query\Tests\Integration;

use Devly\WP\Query\TermQuery;
use WP_UnitTestCase;

class TermQueryTest extends WP_UnitTestCase
{
    protected TermQuery $query;

    public function setUp(): void
    {
        $this->query = TermQuery::create()
            ->hideEmpty(false);
    }

    public function tearDown(): void
    {
        unset($this->query);
    }

    public function testGetAllByTaxonomyName(): void
    {
        $this->query->whereTaxonomy('category');

        $this->assertEquals(1, $this->query->get()->count());
    }

    public function testGetTermByTermTaxonomyId(): void
    {
        $this->query->whereTermTaxonomyID(1);

        $this->assertEquals(1, $this->query->get()->count());
    }

    public function testGetTermByIdWhereIdIn(): void
    {
        $this->query->whereIdIn(1);

        $this->assertEquals(1, $this->query->get()->count());
    }

    public function testGetTermByTermName(): void
    {
        $query = TermQuery::create()
            ->whereName('Uncategorized')
            ->hideEmpty(false);

        $res = $query->get();

        $this->assertEquals(1, $res->count());
    }

    public function testGetTermBySlug(): void
    {
        $query = TermQuery::create()
            ->whereSlug('uncategorized')
            ->hideEmpty(false);

        $res = $query->get();

        $this->assertEquals(1, $res->count());
    }
}
