<?php

declare(strict_types=1);

namespace Devly\WP\Query;

use Devly\Utils\Arr;
use Devly\WP\Query\Concerns\HasDateQuery;
use Devly\WP\Query\Concerns\HasMetaQuery;
use Devly\WP\Query\Concerns\HasWhere;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

use function collect;
use function func_get_args;
use function func_num_args;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;

class UserQuery extends Builder
{
    use HasWhere;
    use HasDateQuery;
    use HasMetaQuery;

    /**
     * -----------------------------------------------------
     * User Role
     * -----------------------------------------------------
     * Show users associated with certain role.
     */

    /**
     * Match users by role name inclusive
     *
     * Note that this is an inclusive list: users must match *each* role
     *
     * @param string|string[] $role An array or a comma-separated list of role
     *                              names that users must match to be included
     *                              in results.
     */
    public function whereRole($role): self
    {
        if (func_num_args() > 1) {
            $role = func_get_args();
        }

        if (
            is_array($role) && ! Arr::every($role, static fn ($value) => is_string($value))
            || ! is_array($role) && ! is_string($role) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('User role must be a string or a list of strings.');
        }

        return $this->set('role', $role);
    }

    /**
     * Match users by role name
     *
     * Matched users must have at least one of these roles.
     *
     * @param string|string[] $role An array of role names.
     */
    public function whereRoleIn($role): self
    {
        $role = is_array($role) ? $role : func_get_args();

        if (
            is_array($role) && ! Arr::every($role, static fn ($value) => is_string($value))
            || ! is_array($role) && ! is_string($role) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('User role must be a string or a list of strings.');
        }

        return $this->whereIn('role', $role);
    }

    /**
     * Exclude users by role name
     *
     * Users matching one or more of these roles will not be included
     * in results.
     *
     * @param string|string[] $role An array of role names to exclude.
     */
    public function whereRoleNotIn($role): self
    {
        $role = is_array($role) ? $role : func_get_args();

        if (
            is_array($role) && ! Arr::every($role, static fn ($value) => is_string($value))
            || ! is_array($role) && ! is_string($role) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('User role must be a string or a list of strings.');
        }

        return $this->whereNotIn('role', $role);
    }

    /**
     * -----------------------------------------------------
     * Include & Exclude
     * -----------------------------------------------------
     * Show specific users.
     */

    /**
     * Include users by user ID
     *
     * @param int|int[] $ids List of users to be included.
     */
    public function includeUsers($ids): self
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        if (
            is_array($ids) && ! Arr::every($ids, static fn ($value) => is_int($value))
            || ! is_array($ids) && ! is_int($ids) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('User ID must be an integer or a list of integers.');
        }

        return $this->set('include', $ids);
    }

    /**
     * Include users by user ID
     *
     * @param int|int[] $ids List of users to be included.
     */
    public function whereUserIn($ids): self
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        if (
            is_array($ids) && ! Arr::every($ids, static fn ($value) => is_int($value))
            || ! is_array($ids) && ! is_int($ids) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('User ID must be an integer or a list of integers.');
        }

        return $this->includeUsers($ids);
    }

    /**
     * Exclude users by user ID
     *
     * @param int|int[] $ids List of users to be excluded.
     */
    public function excludeUsers($ids): self
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        if (
            is_array($ids) && ! Arr::every($ids, static fn ($value) => is_int($value))
            || ! is_array($ids) && ! is_int($ids) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('User ID must be an integer or a list of integers.');
        }

        return $this->set('exclude', $ids);
    }

    /**
     * Exclude users by user ID
     *
     * @param int|int[] $ids List of users to be excluded.
     */
    public function whereUserNotIn($ids): self
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        if (
            is_array($ids) && ! Arr::every($ids, static fn ($value) => is_int($value))
            || ! is_array($ids) && ! is_int($ids) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('User ID must be an integer or a list of integers.');
        }

        return $this->excludeUsers($ids);
    }

    /**
     * -----------------------------------------------------
     * Blog Parameters
     * -----------------------------------------------------
     */

    /**
     * Show users associated with certain blog on the network
     *
     * @param int $id The blog id on a multisite environment.
     */
    public function whereBlogID(int $id): self
    {
        return $this->set('blog_id', $id);
    }

    /**
     * -----------------------------------------------------
     * Search Parameters
     * -----------------------------------------------------
     * Search users
     */

    /**
     * Searches for possible string matches on columns
     *
     * @param string          $query   Use of the * wildcard before and/orafter
     *                                 the string will match on columns starting
     *                                 with*, *ending with, or *containing* the
     *                                 string you enter.
     * @param string|string[] $columns List of database table columns to matches
     *                                 the search string across multiple columns.
     *                                 - ‘ID‘ – Search by user id.
     *                                 - ‘user_login‘ – Search by user login.
     *                                 - ‘user_nicename‘ – Search by user nicename.
     *                                 - user_email‘ – Search by user email.
     *                                 - ‘user_url‘ – Search by user url.
     */
    public function search(string $query, $columns = ''): self
    {
        if (! empty($columns)) {
            $this->set('search_columns', $columns);
        }

        return $this->set('search', $query);
    }

    public function searchByID(string $id): self
    {
        return $this->search($id, 'ID');
    }

    public function searchByUsername(string $username): self
    {
        return $this->search($username, 'user_login');
    }

    public function searchByNicename(string $nicename): self
    {
        return $this->search($nicename, 'user_login');
    }

    public function searchByEmail(string $email): self
    {
        return $this->search($email, 'user_email');
    }

    public function searchByUserUrl(string $url): self
    {
        return $this->search($url, 'user_url');
    }

    /**
     * -----------------------------------------------------
     * Pagination Parameters
     * -----------------------------------------------------
     * Limit retrieved Users.
     */

    /**
     * Limit the number of returned results
     */
    public function limit(int $limit): self
    {
        return $this->set('number', $limit);
    }

    /**
     * Number of users to skip.
     */
    public function skip(int $skip): self
    {
        return $this->set('offset', $skip);
    }

    /**
     * Paginate the results
     *
     * @param int $perPage The maximum returned number of results
     * @param int $paged   Defines the page of results to return.
     */
    public function paginate(int $perPage, int $paged = 1): self
    {
        $this->set('number', $perPage);
        $this->set('paged', $paged);

        return $this;
    }

    /**
     * -----------------------------------------------------
     * Order Parameters
     * -----------------------------------------------------
     */

    /**
     * Sort retrieved Users.
     *
     * @param string|string[] $parameter Sort retrieved users by parameter. It can
     *                                   be a string with a single field, a string
     *                                   containing a list of values separated by
     *                                   commas or spaces, or an array with fields.
     * @param string          $order     Designates the ascending or descending order.
     */
    public function orderBy($parameter, string $order = 'ASC'): self
    {
        $this->set('orderby', $parameter);
        $this->set('order', $order);

        return $this;
    }

    /**
     * Sort retrieved users in descending order.
     *
     * @param string|string[] $parameter Sort retrieved users by parameter. It can
     *                                   be a string with a single field, a string
     *                                   containing a list of values separated by
     *                                   commas or spaces, or an array with fields.
     */
    public function orderByDesc($parameter): self
    {
        $this->orderBy($parameter, 'DESC');

        return $this;
    }

    public function orderByID(string $order = 'ASC'): self
    {
        return $this->orderBy('ID', $order);
    }

    public function orderByDisplayName(string $order = 'ASC'): self
    {
        return $this->orderBy('display_name', $order);
    }

    public function orderByUsername(string $order = 'ASC'): self
    {
        return $this->orderBy('user_name', $order);
    }

    public function orderByUserLogin(string $order = 'ASC'): self
    {
        return $this->orderBy('user_login', $order);
    }

    public function orderByNicename(string $order = 'ASC'): self
    {
        return $this->orderBy('user_nicename', $order);
    }

    public function orderByEmail(string $order = 'ASC'): self
    {
        return $this->orderBy('user_email', $order);
    }

    public function orderByUserUrl(string $order = 'ASC'): self
    {
        return $this->orderBy('user_url', $order);
    }

    public function orderByRegisteredDate(string $order = 'ASC'): self
    {
        return $this->orderBy('user_registered', $order);
    }

    public function orderByPostCount(string $order = 'ASC'): self
    {
        return $this->orderBy('post_count', $order);
    }

    /**
     * -----------------------------------------------------
     * Custom Field (user meta) Parameters
     * -----------------------------------------------------
     * Show users associated with a certain custom field.
     */

    /**
     * Limit using meta key.
     *
     * @param mixed $value
     */
    public function whereMetaKey(string $key, $value, ?string $compare = null): self
    {
        if (isset($compare)) {
            $this->set('meta_compare', $compare);
        }

        $this->set('meta_key', $key);

        return $this->set('meta_value', $value);
    }

    /**
     * Display posts based on custom field value, regardless of the custom field key.
     *
     * @param mixed $value
     */
    public function whereMetaValue($value): self
    {
        $this->set('meta_value', $value);

        return $this;
    }

    public function whereIsAuthor(): self
    {
        return $this->set('who', 'authors');
    }

    /**
     * @param true|string[] $types Pass an array of post types to filter results
     *                             to users who have published posts in those
     *                             post types. true is an alias for all public
     *                             post types.
     */
    public function whereHasPublishedPosts($types = true): self
    {
        $types = is_array($types) || func_num_args() === 1 ? $types : func_get_args();

        if (
            is_array($types) && ! Arr::every($types, static fn ($value) => is_string($value))
            || ! is_array($types) && ! is_string($types) && ! is_bool($types) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('Method "' . __METHOD__ . '()" expects a post type'
            . ' name string, a list of post type names or true to check all public post types.');
        }

        return $this->set('has_published_posts', $types);
    }

    /**
     * Set which fields to return
     *
     * @param string|string[] $fields
     */
    public function returnFields($fields): self
    {
        $types = is_array($fields) || func_num_args() === 1 ? $fields : func_get_args();

        if (
            is_array($types) && ! Arr::every($types, static fn ($value) => is_string($value))
            || ! is_array($types) && ! is_string($types) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('Return field must be a string or a list of strings.');
        }

        return $this->set('fields', $fields);
    }

    /**
     * Retrieves all the users matching the query.
     *
     * @param string|null $returnObject Class name of the expected object type to return.
     *                                  If not set, will return a collection of WP_User
     *                                  objects or user IDs if query set to return IDs.
     */
    public function get(?string $returnObject = null): Collection
    {
        $users = get_users($this->getQueryArgs());

        $users = collect($users);

        if (empty($returnObject)) {
            return $users;
        }

        try {
            new ReflectionClass($returnObject);
        } catch (ReflectionException $e) {
            throw new RuntimeException($e->getMessage());
        }

        return $users->map(static fn ($user) => new $returnObject($user));
    }
}
