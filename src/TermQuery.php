<?php

declare(strict_types=1);

namespace Devly\WP\Query;

use Devly\Utils\Arr;
use Devly\WP\Query\Concerns\HasMetaQuery;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use WP_Error;
use WP_Term;

use function func_get_args;
use function func_num_args;
use function is_array;
use function is_int;
use function is_string;
use function strtoupper;

class TermQuery extends Builder
{
    use HasMetaQuery;

    /** @param string|string[] $taxonomy */
    public function whereTaxonomy($taxonomy): self
    {
        $taxonomies = is_array($taxonomy) || func_num_args() === 1 ? $taxonomy : func_get_args();

        if (
            is_array($taxonomy) && ! Arr::every($taxonomy, static fn ($value) => is_string($value))
            || ! is_array($taxonomy) && ! is_string($taxonomy) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('Taxonomy must be a string or a list of strings.');
        }

        $this->set('taxonomy', $taxonomies);

        return $this;
    }

    /** @param int|int[] $ids */
    public function whereObjectIDs($ids): self
    {
        $ids = is_array($ids) || func_num_args() === 1 ? $ids : func_get_args();

        if (
            is_array($ids) && ! Arr::every($ids, static fn ($value) => is_int($value))
            || ! is_array($ids) && ! is_int($ids) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('The method parameter must be an integer or a list of integers.');
        }

        return $this->set('object_ids', $ids);
    }

    public function order(string $order = 'asc'): self
    {
        return $this->set('order', strtoupper($order));
    }

    public function orderDesc(): self
    {
        return $this->set('order', 'DESC');
    }

    public function orderBy(string $field, string $order = 'asc'): self
    {
        $this->set('orderby', $field);
        $this->order($order);

        return $this;
    }

    public function orderByID(string $order = 'asc'): self
    {
        return $this->orderBy('id', $order);
    }

    public function orderByTermID(string $order = 'asc'): self
    {
        return $this->orderBy('term_id', $order);
    }

    public function orderByName(string $order = 'asc'): self
    {
        return $this->orderBy('name', $order);
    }

    public function orderBySlug(string $order = 'asc'): self
    {
        return $this->orderBy('slug', $order);
    }

    /**
     * Order using the number of objects associated with the term.
     */
    public function orderByTermCount(string $order = 'asc'): self
    {
        return $this->orderBy('count', $order);
    }

    public function orderByParent(string $order = 'asc'): self
    {
        return $this->orderBy('parent', $order);
    }

    /**
     * Match the 'order' of the `$include` param.
     */
    public function orderByInclude(string $order = 'asc'): self
    {
        return $this->orderBy('parent', $order);
    }

    /**
     * Match the 'order' of the `$slug` param.
     */
    public function orderBySlugIn(string $order = 'asc'): self
    {
        return $this->orderBy('slug__in', $order);
    }

    public function orderByMateValue(string $order = 'asc'): self
    {
        return $this->orderBy('meta_value', $order);
    }

    public function orderByMateValueNum(string $order = 'asc'): self
    {
        return $this->orderBy('meta_value_num', $order);
    }

    public function hideEmpty(bool $hide = true): self
    {
        return $this->set('hide_empty', $hide);
    }

    /** @param int|int[] $ids */
    public function include($ids): self
    {
        $ids = is_array($ids) || func_num_args() === 1 ? $ids : func_get_args();

        if (
            is_array($ids) && ! Arr::every($ids, static fn ($value) => is_int($value))
            || ! is_array($ids) && ! is_int($ids) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('The method parameter must be an integer or a list of integers.');
        }

        return $this->set('include', $ids);
    }

    /** @param int|int[] $ids */
    public function whereIdIn($ids): self
    {
        $ids = is_array($ids) || func_num_args() === 1 ? $ids : func_get_args();

        if (
            is_array($ids) && ! Arr::every($ids, static fn ($value) => is_int($value))
            || ! is_array($ids) && ! is_int($ids) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('Term ID must be an integer or a list of integers.');
        }

        return $this->include($ids);
    }

    /** @param int|int[] $ids */
    public function exclude($ids): self
    {
        $ids = is_array($ids) || func_num_args() === 1 ? $ids : func_get_args();

        if (
            is_array($ids) && ! Arr::every($ids, static fn ($value) => is_int($value))
            || ! is_array($ids) && ! is_int($ids) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('Excluded term ID must be an integer or a list of integers.');
        }

        return $this->set('exclude', $ids);
    }

    /** @param int|int[] $id */
    public function whereIdNotIn($id): self
    {
        $id = is_array($id) || func_num_args() === 1 ? $id : func_get_args();

        if (
            is_array($id) && ! Arr::every($id, static fn ($value) => is_int($value))
            || ! is_array($id) && ! is_int($id) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('Excluded term ID must be an integer or a list of integers.');
        }

        return $this->exclude($id);
    }

    /** @param int|int[] $ids */
    public function excludeTree($ids): self
    {
        $ids = is_array($ids) || func_num_args() === 1 ? $ids : func_get_args();

        if (
            is_array($ids) && ! Arr::every($ids, static fn ($value) => is_int($value))
            || ! is_array($ids) && ! is_int($ids) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('Excluded ID must be an integer or a list of integers.');
        }

        return $this->set('exclude_tree', $ids);
    }

    public function limit(int $limit): self
    {
        return $this->set('number', $limit);
    }

    public function offset(int $offset): self
    {
        return $this->set('offset', $offset);
    }

    public function skip(int $offset): self
    {
        return $this->offset($offset);
    }

    /**
     * Term fields to query for.
     */
    public function fields(string $fields): self
    {
        return $this->set('fields', $fields);
    }

    /**
     * Name or array of names to return term(s) for.
     *
     * @param string|string[] $name
     */
    public function whereName($name): self
    {
        $name = is_array($name) || func_num_args() === 1 ? $name : func_get_args();

        if (
            is_array($name) && ! Arr::every($name, static fn ($value) => is_string($value))
            || ! is_array($name) && ! is_string($name) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('Term name must be a string or a list of strings.');
        }

        return $this->set('name', $name);
    }

    /**
     * Slug or array of slugs to return term(s) for.
     *
     * @param string|string[] $slug
     */
    public function whereSlug($slug): self
    {
        $slug = is_array($slug) || func_num_args() === 1 ? $slug : func_get_args();

        if (
            is_array($slug) && ! Arr::every($slug, static fn ($value) => is_string($value))
            || ! is_array($slug) && ! is_string($slug) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('Term slug must be a string or a list of strings.');
        }

        return $this->set('slug', $slug);
    }

    /**
     * erm taxonomy ID, or array of term taxonomy IDs, to match when querying terms.
     *
     * @param int|int[] $ids
     */
    public function whereTermTaxonomyID($ids): self
    {
        $ids = is_array($ids) || func_num_args() === 1 ? $ids : func_get_args();

        if (
            is_array($ids) && ! Arr::every($ids, static fn ($value) => is_int($value))
            || ! is_array($ids) && ! is_int($ids) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('Term taxonomy ID must be an integer or a list of integers.');
        }

        return $this->set('term_taxonomy_id', $ids);
    }

    /**
     * Whether to include terms that have non-empty descendants (even if $hide_empty is set to true). Default true.
     */
    public function whereHierarchical(bool $hierarchical = true): self
    {
        return $this->set('hierarchical', $hierarchical);
    }

    /**
     * Search criteria to match terms. Will be SQL-formatted with wildcards before and after.
     */
    public function search(string $search): self
    {
        return $this->set('search', $search);
    }

    /**
     * Retrieve terms with criteria by which a term is LIKE $name__like.
     */
    public function whereNameLike(string $search): self
    {
        return $this->set('name__like', $search);
    }

    public function whereDescriptionLike(string $search): self
    {
        return $this->set('description__like', $search);
    }

    public function padCounts(bool $counts = true): self
    {
        return $this->set('pad_counts', $counts);
    }

    /**
     * Term ID to retrieve child terms of.
     *
     * If multiple taxonomies are passed, $child_of is ignored. Default 0.
     */
    public function whereChildOf(int $id): self
    {
        return $this->set('child_of', $id);
    }

    /**
     * Parent term ID to retrieve direct-child terms of.
     */
    public function whereParent(int $id): self
    {
        return $this->set('parent', $id);
    }

    /**
     * True to limit results to terms that have no children.
     *
     * This parameter has no effect on non-hierarchical taxonomies. Default false.
     */
    public function whereChildless(bool $childless = true): self
    {
        return $this->set('childless', $childless);
    }

    /**
     * Unique cache key to be produced when this query is stored in an object cache. Default 'core'.
     */
    public function cacheDomain(string $domain): self
    {
        return $this->set('cache_domain', $domain);
    }

    /**
     * Whether to prime meta caches for matched terms. Default true.
     */
    public function updateTermMetaCache(bool $cache = true): self
    {
        return $this->set('update_term_meta_cache', $cache);
    }

    /**
     * Meta value or values to filter by.
     *
     * @param string|string[] $value
     */
    public function whereMetaValue($value): self
    {
        $values = is_array($value) || func_num_args() === 1 ? $value : func_get_args();

        if (
            is_array($values) && ! Arr::every($values, static fn ($value) => is_string($value))
            || ! is_array($values) && ! is_string($values) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('Meta value must be a string or a list of strings.');
        }

        return $this->set('meta_value', $value);
    }

    /**
     * Meta  key or keys to filter by.
     *
     * @param string|string[] $key
     */
    public function whereMetaKey($key): self
    {
        $key = is_array($key) || func_num_args() === 1 ? $key : func_get_args();

        if (
            is_array($key) && ! Arr::every($key, static fn ($value) => is_string($value))
            || ! is_array($key) && ! is_string($key) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('Meta key must be a string or a list of strings.');
        }

        return $this->set('meta_key', $key);
    }

    /**
     * MySQL operator used for comparing the meta value.
     *
     * See WP_Meta_Query::__construct for accepted values and default value.
     */
    public function metaCompare(string $operator): self
    {
        return $this->set('meta_compare', Helpers::parseCompareOperator($operator));
    }

    /**
     * MySQL operator used for comparing the meta key.
     *
     * See WP_Meta_Query::__construct for accepted values and default value.
     */
    public function metaCompareKey(string $operator): self
    {
        return $this->set('meta_compare_key', Helpers::parseCompareOperator($operator));
    }

    /**
     * MySQL data type that the meta_value column will be CAST to for comparisons.
     *
     * See WP_Meta_Query::__construct for accepted values and default value.
     */
    public function metaType(string $type): self
    {
        return $this->set('meta_type', $type);
    }

    /**
     * MySQL data type that the meta_key column will be CAST to for comparisons.
     *
     * See WP_Meta_Query::__construct for accepted values and default value.
     */
    public function metaTypeKey(string $type): self
    {
        return $this->set('meta_type_key', $type);
    }

    public function count(): int
    {
        $count = wp_count_terms($this->getQueryArgs());
        if ($count instanceof WP_Error) {
            throw new RuntimeException($count->get_error_message(), (int) $count->get_error_code());
        }

        return (int) $count;
    }

    public function get(?string $model = null): Collection
    {
        $terms = get_terms($this->getQueryArgs());

        if ($terms instanceof WP_Error) {
            throw new RuntimeException($terms->get_error_message(), (int) $terms->get_error_code());
        }

        $collection = Collection::make($terms);

        if (empty($model)) {
            return $collection;
        }

        try {
            new ReflectionClass($model);
        } catch (ReflectionException $e) {
            throw new RuntimeException($e->getMessage());
        }

        return $collection->map(static fn (WP_Term $term) => new $model($term));
    }
}
