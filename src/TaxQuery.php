<?php

declare(strict_types=1);

namespace Devly\WP\Query;

use Closure;

use function call_user_func;

class TaxQuery extends Builder
{
    /**
     * @param string|Closure(self): void $taxonomy Taxonomy name or a Closure
     * @param string|null                $field    Select taxonomy term by. Possible values are ‘term_id’,‘name’,
     *                                             ‘slug’ or ‘term_taxonomy_id’.
     * @param int|string|string[]        $terms    Taxonomy term(s).
     * @param string                     $operator Operator to test. Possible values are ‘IN’, ‘NOT IN’, ‘AND’,
     *                                             ‘EXISTS’ and ‘NOT EXISTS’.
     * @param bool                       $children Whether to include children for hierarchical taxonomies.
     */
    public function where(
        $taxonomy,
        ?string $field = null,
        $terms = null,
        string $operator = 'in',
        bool $children = true
    ): self {
        if ($taxonomy instanceof Closure) {
            $callback = $taxonomy;
            $taxonomy = new self();
            call_user_func($callback, $taxonomy);
        }

        if (! empty($this->query) && ! isset($this->query['relation'])) {
            $this->query['relation'] = 'AND';
        }

        if ($taxonomy instanceof self) {
            if (empty($this->query)) {
                $this->query = $taxonomy->getQueryArgs();
            } else {
                $this->query[] = $taxonomy->getQueryArgs();
            }

            return $this;
        }

        $this->query[] = [
            'taxonomy'         => $taxonomy,
            'field'            => $field,
            'terms'            => $terms,
            'include_children' => $children,
            'operator'         => Helpers::parseCompareOperator($operator),
        ];

        return $this;
    }

    /**
     * @param string|Closure(self): void $taxonomy Taxonomy name or a Closure
     * @param string|null                $field    Select taxonomy term by. Possible values are ‘term_id’,‘name’,
     *                                             ‘slug’ or ‘term_taxonomy_id’.
     * @param int|string|string[]        $terms    Taxonomy term(s).
     * @param string                     $operator Operator to test. Possible values are ‘IN’, ‘NOT IN’, ‘AND’,
     *                                             ‘EXISTS’ and ‘NOT EXISTS’.
     * @param bool                       $children Whether to include children for hierarchical taxonomies.
     */
    public function andWhere(
        $taxonomy,
        ?string $field = null,
        $terms = null,
        string $operator = 'in',
        bool $children = true
    ): self {
        if (! isset($this->query['relation']) || $this->query['relation'] !== 'AND') {
            $this->query['relation'] = 'AND';
        }

        return $this->where($taxonomy, $field, $terms, $operator, $children);
    }

    /**
     * @param string|Closure(self): void $taxonomy Taxonomy name or a Closure
     * @param string|null                $field    Select taxonomy term by. Possible values are ‘term_id’,‘name’,
     *                                             ‘slug’ or ‘term_taxonomy_id’.
     * @param int|string|string[]        $terms    Taxonomy term(s).
     * @param string                     $operator Operator to test. Possible values are ‘IN’, ‘NOT IN’, ‘AND’,
     *                                             ‘EXISTS’ and ‘NOT EXISTS’.
     * @param bool                       $children Whether to include children for hierarchical taxonomies.
     */
    public function orWhere(
        $taxonomy,
        ?string $field = null,
        $terms = null,
        string $operator = 'in',
        bool $children = true
    ): self {
        if (! isset($this->query['relation']) || $this->query['relation'] !== 'OR') {
            $this->query['relation'] = 'OR';
        }

        return $this->where($taxonomy, $field, $terms, $operator, $children);
    }

    /**
     * @param string              $taxonomy Taxonomy name or a Closure
     * @param string              $field    Select taxonomy term by. Possible values are ‘term_id’,‘name’,
     *                                      ‘slug’ or ‘term_taxonomy_id’.
     * @param int|string|string[] $terms    Taxonomy term(s).
     * @param bool                $children Whether to include children for hierarchical taxonomies.
     */
    public function whereIn(string $taxonomy, string $field, $terms, bool $children = true): self
    {
        return $this->where($taxonomy, $field, $terms, 'in', $children);
    }

    /**
     * @param string              $taxonomy Taxonomy name or a Closure
     * @param string              $field    Select taxonomy term by. Possible values are ‘term_id’,‘name’,
     *                                      ‘slug’ or ‘term_taxonomy_id’.
     * @param int|string|string[] $terms    Taxonomy term(s).
     * @param bool                $children Whether to include children for hierarchical taxonomies.
     */
    public function andWhereIn(string $taxonomy, string $field, $terms, bool $children = true): self
    {
        return $this->andWhere($taxonomy, $field, $terms, 'in', $children);
    }

    /**
     * @param string              $taxonomy Taxonomy name or a Closure
     * @param string              $field    Select taxonomy term by. Possible values are ‘term_id’,‘name’,
     *                                      ‘slug’ or ‘term_taxonomy_id’.
     * @param int|string|string[] $terms    Taxonomy term(s).
     * @param bool                $children Whether to include children for hierarchical taxonomies.
     */
    public function orWhereIn(string $taxonomy, string $field, $terms, bool $children = true): self
    {
        return $this->orWhere($taxonomy, $field, $terms, 'in', $children);
    }

    /**
     * @param string              $taxonomy Taxonomy name or a Closure
     * @param string              $field    Select taxonomy term by. Possible values are ‘term_id’,‘name’,
     *                                      ‘slug’ or ‘term_taxonomy_id’.
     * @param int|string|string[] $terms    Taxonomy term(s).
     * @param bool                $children Whether to include children for hierarchical taxonomies.
     */
    public function whereNotIn(string $taxonomy, string $field, $terms, bool $children = true): self
    {
        return $this->where($taxonomy, $field, $terms, '!in', $children);
    }

    /**
     * @param string              $taxonomy Taxonomy name or a Closure
     * @param string              $field    Select taxonomy term by. Possible values are ‘term_id’,‘name’,
     *                                      ‘slug’ or ‘term_taxonomy_id’.
     * @param int|string|string[] $terms    Taxonomy term(s).
     * @param bool                $children Whether to include children for hierarchical taxonomies.
     */
    public function andWhereNotIn(string $taxonomy, string $field, $terms, bool $children = true): self
    {
        return $this->andWhere($taxonomy, $field, $terms, '!in', $children);
    }

    /**
     * @param string              $taxonomy Taxonomy name or a Closure
     * @param string              $field    Select taxonomy term by. Possible values are ‘term_id’,‘name’,
     *                                      ‘slug’ or ‘term_taxonomy_id’.
     * @param int|string|string[] $terms    Taxonomy term(s).
     * @param bool                $children Whether to include children for hierarchical taxonomies.
     */
    public function orWhereNotIn(string $taxonomy, string $field, $terms, bool $children = true): self
    {
        return $this->orWhere($taxonomy, $field, $terms, '!in', $children);
    }

    /**
     * @param string              $taxonomy Taxonomy name or a Closure
     * @param string              $field    Select taxonomy term by. Possible values are ‘term_id’,‘name’,
     *                                      ‘slug’ or ‘term_taxonomy_id’.
     * @param int|string|string[] $terms    Taxonomy term(s).
     * @param bool                $children Whether to include children for hierarchical taxonomies.
     */
    public function whereExists(string $taxonomy, string $field, $terms, bool $children = true): self
    {
        return $this->where($taxonomy, $field, $terms, 'exists', $children);
    }

    /**
     * @param string              $taxonomy Taxonomy name or a Closure
     * @param string              $field    Select taxonomy term by. Possible values are ‘term_id’,‘name’,
     *                                      ‘slug’ or ‘term_taxonomy_id’.
     * @param int|string|string[] $terms    Taxonomy term(s).
     * @param bool                $children Whether to include children for hierarchical taxonomies.
     */
    public function andWhereExists(string $taxonomy, string $field, $terms, bool $children = true): self
    {
        return $this->andWhere($taxonomy, $field, $terms, 'exists', $children);
    }

    /**
     * @param string              $taxonomy Taxonomy name or a Closure
     * @param string              $field    Select taxonomy term by. Possible values are ‘term_id’,‘name’,
     *                                      ‘slug’ or ‘term_taxonomy_id’.
     * @param int|string|string[] $terms    Taxonomy term(s).
     * @param bool                $children Whether to include children for hierarchical taxonomies.
     */
    public function orWhereExists(string $taxonomy, string $field, $terms, bool $children = true): self
    {
        return $this->andWhere($taxonomy, $field, $terms, 'exists', $children);
    }

    /**
     * @param string              $taxonomy Taxonomy name or a Closure
     * @param string              $field    Select taxonomy term by. Possible values are ‘term_id’,‘name’,
     *                                      ‘slug’ or ‘term_taxonomy_id’.
     * @param int|string|string[] $terms    Taxonomy term(s).
     * @param bool                $children Whether to include children for hierarchical taxonomies.
     */
    public function whereNotExists(string $taxonomy, string $field, $terms, bool $children = true): self
    {
        return $this->where($taxonomy, $field, $terms, '!exists', $children);
    }

    /**
     * @param string              $taxonomy Taxonomy name or a Closure
     * @param string              $field    Select taxonomy term by. Possible values are ‘term_id’,‘name’,
     *                                      ‘slug’ or ‘term_taxonomy_id’.
     * @param int|string|string[] $terms    Taxonomy term(s).
     * @param bool                $children Whether to include children for hierarchical taxonomies.
     */
    public function andWhereNotExists(string $taxonomy, string $field, $terms, bool $children = true): self
    {
        return $this->andWhere($taxonomy, $field, $terms, '!exists', $children);
    }

    /**
     * @param string              $taxonomy Taxonomy name or a Closure
     * @param string              $field    Select taxonomy term by. Possible values are ‘term_id’,‘name’,
     *                                      ‘slug’ or ‘term_taxonomy_id’.
     * @param int|string|string[] $terms    Taxonomy term(s).
     * @param bool                $children Whether to include children for hierarchical taxonomies.
     */
    public function orWhereNotExists(string $taxonomy, string $field, $terms, bool $children = true): self
    {
        return $this->orWhere($taxonomy, $field, $terms, '!exists', $children);
    }
}
