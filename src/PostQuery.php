<?php

declare(strict_types=1);

namespace Devly\WP\Query;

use Closure;
use Devly\Exceptions\ObjectNotFoundException;
use Devly\Utils\Arr;
use Devly\WP\Query\Concerns\HasDateQuery;
use Devly\WP\Query\Concerns\HasMetaQuery;
use Devly\WP\Query\Concerns\HasWhere;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use WP_Post;

use function array_filter;
use function call_user_func;
use function collect;
use function func_get_args;
use function func_num_args;
use function in_array;
use function is_array;
use function is_int;
use function is_string;
use function sprintf;
use function strlen;
use function strncmp;
use function strtoupper;

class PostQuery extends Builder
{
    use HasMetaQuery;
    use HasDateQuery;
    use HasWhere;

    /**
     * -----------------------------------------------------
     * Post Type Parameters
     * -----------------------------------------------------
     */

    /**
     * Create new WPQuery instance for the specified post type\types.
     *
     * @param string|string[] $name The post type name
     */
    public function wherePostType($name): self
    {
        return $this->set('post_type', $name);
    }

    /**
     * -----------------------------------------------------
     * Author Parameters
     * -----------------------------------------------------
     */

    /**
     * Display posts by author, using author ID.
     */
    public function whereAuthor(int $id): self
    {
        return $this->set('author', $id);
    }

    /**
     * Display posts by author, using author ‘user_nicename‘.
     */
    public function whereAuthorName(string $name): self
    {
        return $this->set('author_name', $name);
    }

    /**
     * Display posts from multiple authors.
     *
     * @param int|int[] $id
     */
    public function whereAuthorIn($id): self
    {
        $id = is_array($id) ? $id : func_get_args();

        if (! Arr::every($id, static fn ($value) => is_int($value))) {
            throw new InvalidArgumentException('Author id must be an int or a list of integer ids');
        }

        return $this->whereIn('author', $id);
    }

    /**
     * Exclude posts from multiple authors
     *
     * @param int|int[] $id
     */
    public function whereAuthorNotIn($id): self
    {
        $id = is_array($id) ? $id : func_get_args();

        if (! Arr::every($id, static fn ($value) => is_int($value))) {
            throw new InvalidArgumentException('Author id must be an int or a list of integer ids');
        }

        return $this->whereNotIn('author', $id);
    }

    /**
     * Exclude posts from multiple authors
     *
     * Alias to whereAuthorNotIn() method.
     *
     * @param int|int[] $id
     */
    public function excludeAuthors($id): self
    {
        $id = is_array($id) ? $id : func_get_args();

        if (! Arr::every($id, static fn ($value) => is_int($value))) {
            throw new InvalidArgumentException('Author id must be an int or a list of integer ids');
        }

        return $this->whereAuthorNotIn($id);
    }

    /**
     * -----------------------------------------------------
     * Category Parameters
     * -----------------------------------------------------
     */

    /**
     * Display posts that have one category (and any children of that category), using category id.
     */
    public function whereCategoryID(int $id): self
    {
        return $this->set('cat', $id);
    }

    /**
     * Display posts that have this category (and any children of that category) using category slug.
     *
     * @param string $name Category slug.
     */
    public function whereCategory(string $name): self
    {
        return $this->whereCategoryName($name);
    }

    /**
     * Display posts that have this category (and any children of that category) using category slug.
     *
     * @param string $name Category slug.
     */
    public function whereCategoryName(string $name): self
    {
        return $this->set('category_name', $name);
    }

    /**
     * Display posts that are in multiple categories.
     *
     * @param int|int[] $ids Category IDs
     */
    public function whereCategoryAnd($ids): self
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        if (! Arr::every($ids, static fn ($value) => is_int($value))) {
            throw new InvalidArgumentException('Category id must be an int or a list of integer ids');
        }

        return $this->whereAnd('category', $ids);
    }

    /**
     * Display posts that have one of the specified categories.
     *
     * @param int|int[] $ids Category IDs
     */
    public function whereCategoryIn($ids): self
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        if (! Arr::every($ids, static fn ($value) => is_int($value))) {
            throw new InvalidArgumentException('Category id must be an int or a list of integer ids');
        }

        return $this->whereIn('category', $ids);
    }

    /**
     * Exclude posts that do not have one of the specified categories.
     *
     * @param int|int[] $ids A list of category IDs to excluded
     */
    public function whereCategoryNotIn($ids): self
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        if (! Arr::every($ids, static fn ($value) => is_int($value))) {
            throw new InvalidArgumentException('Category id must be an int or a list of integer ids');
        }

        return $this->whereNotIn('category', $ids);
    }

    /**
     * Exclude posts that do not have one of the specified categories.
     *
     * Alias to whereCategoryNotIn() method.
     *
     * @param int|int[] $ids A list of category IDs to excluded
     */
    public function excludeCategories($ids): self
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        if (! Arr::every($ids, static fn ($value) => is_int($value))) {
            throw new InvalidArgumentException('Category id must be an int or a list of integer ids');
        }

        return $this->whereCategoryNotIn($ids);
    }

    /**
     * -----------------------------------------------------
     * Tag Parameters
     * -----------------------------------------------------
     */

    /**
     * Display posts that have specific tag, using tag ID.
     */
    public function whereTagID(int $id): self
    {
        return $this->set('tag_id', $id);
    }

    /**
     * Display posts that have specific tag, using tag slug.
     */
    public function whereTag(string $name): self
    {
        return $this->whereTagName($name);
    }

    /**
     * Display posts that have specific tag, using tag slug.
     */
    public function whereTagName(string $slug): self
    {
        return $this->set('tag', $slug);
    }

    /**
     * Display posts that have “all” of these tags, using tag ID.
     *
     * @param int|int[] $ids
     */
    public function whereTagAnd($ids): self
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        if (! Arr::every($ids, static fn ($value) => is_int($value))) {
            throw new InvalidArgumentException('Tag id must be an int or a list of integer ids');
        }

        return $this->whereAnd('tag', $ids);
    }

    /**
     * Display posts that have “either” of these tags, using tag ID.
     *
     * @param int|int[] $ids
     */
    public function whereTagIn($ids): self
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        if (! Arr::every($ids, static fn ($value) => is_int($value))) {
            throw new InvalidArgumentException('Tag id must be an int or a list of integer ids');
        }

        return $this->whereIn('tag', $ids);
    }

    /**
     * Exclude posts that have “either” of these tags, using tag ID.
     *
     * @param int|int[] $ids
     */
    public function whereTagNotIn($ids): self
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        if (! Arr::every($ids, static fn ($value) => is_int($value))) {
            throw new InvalidArgumentException('Tag id must be an int or a list of integer ids');
        }

        return $this->whereNotIn('tag', $ids);
    }

    /**
     * Alias to whereTagNotIn() method.
     *
     * @param int|int[] $ids
     */
    public function excludeByTagID($ids): self
    {
        $ids = is_array($ids) ? $ids : func_get_args();

        if (! Arr::every($ids, static fn ($value) => is_int($value))) {
            throw new InvalidArgumentException('Tag id must be an int or a list of integer ids');
        }

        return $this->whereTagNotIn($ids);
    }

    /**
     * Display posts that have “all” of these tags, using tag slug.
     *
     * @param string|string[] $names
     */
    public function whereTagSlugAnd($names): self
    {
        $names = is_array($names) ? $names : func_get_args();

        if (! Arr::every($names, static fn ($value) => is_string($value))) {
            throw new InvalidArgumentException('Tag slug must be a string or a list of strings.');
        }

        return $this->whereAnd('tag_slug', $names);
    }

    /**
     * Display posts that have “either” of these tags, using tag slug.
     *
     * @param string|string[] $names
     */
    public function whereTagSlugIn($names): self
    {
        $names = is_array($names) ? $names : func_get_args();

        if (! Arr::every($names, static fn ($value) => is_string($value))) {
            throw new InvalidArgumentException('Tag slug must be a string or a list of strings.');
        }

        return $this->whereIn('tag_slug', $names);
    }

    /**
     * -----------------------------------------------------
     * Search Parameters
     * -----------------------------------------------------
     */

    /**
     * Show posts based on a keyword search.
     */
    public function search(string $keyword): self
    {
        return $this->set('s', $keyword);
    }

    /**
     * -----------------------------------------------------
     * Password Parameters
     * -----------------------------------------------------
     */

    /**
     * Password parameters.
     *
     * @param string|bool $password true for posts with passwords, false for posts without
     *                              passwords, string for posts with a particular password.
     */
    public function wherePassword($password): self
    {
        if (is_string($password)) {
            return $this->where('post_password', $password);
        }

        return $this->where('has_password', $password);
    }

    /**
     * Show posts with a particular password.
     */
    public function whereHasPassword(bool $has = true): self
    {
        return $this->wherePassword($has);
    }

    /**
     * -----------------------------------------------------
     * Status Parameters
     * -----------------------------------------------------
     */

    /**
     * Show posts associated with certain post status.
     *
     * @param string|string[] $status
     */
    public function whereStatus($status): self
    {
        $status = is_array($status) || func_num_args() === 1 ? $status : func_get_args();

        if (
            is_array($status) && ! Arr::every($status, static fn ($value) => is_string($value))
            || ! is_array($status) && ! is_string($status) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('Status must be a string or a list of strings.');
        }

        return $this->where('post_status', $status);
    }

    /**
     * Show posts whose status is `publish`.
     */
    public function whereIsPublished(): self
    {
        return $this->whereStatus('publish');
    }

    /**
     * Show posts whose status is `pending`.
     */
    public function whereIsPending(): self
    {
        return $this->whereStatus('pending');
    }

    /**
     * Show posts whose status is `draft`.
     */
    public function whereIsDraft(): self
    {
        return $this->whereStatus('draft');
    }

    /**
     * Show posts whose status is `trash`.
     */
    public function whereIsInTrash(): self
    {
        return $this->whereStatus('trash');
    }

    /**
     * -----------------------------------------------------
     * Comment Parameters
     * -----------------------------------------------------
     */

    /**
     * Filter posts to show using their comments count..
     *
     * @param string|null $compare The search operator. Possible values are ‘=’, ‘!=’, ‘>’,
     *                             ‘>=’, ‘<‘, ‘<=’. Default value is ‘=’.
     */
    public function whereCommentCount(int $count, ?string $compare = null): self
    {
        $value = ! $compare ? $count : [
            'value' => $count,
            'compare' => $compare,
        ];

        return $this->set('comment_count', $value);
    }

    /**
     * -----------------------------------------------------
     * Post & Page Parameters
     * -----------------------------------------------------
     */

    /**
     * Specify post ID to retrieve.
     */
    public function wherePostID(int $id): self
    {
        return $this->set('p', $id);
    }

    /**
     * Specify post IDs to retrieve.
     *
     * @param int|int[] $id
     */
    public function wherePostIn($id): self
    {
        $id = is_array($id) ? $id : func_get_args();

        if (! Arr::every($id, static fn ($value) => is_int($value))) {
            throw new InvalidArgumentException('Post id must be an integer or a list of integers.');
        }

        return $this->whereIn('post', $id);
    }

    /**
     * Specify post IDs NOT to retrieve.
     *
     * @param int|int[] $id
     */
    public function wherePostNotIn($id): self
    {
        $id = is_array($id) ? $id : func_get_args();

        if (! Arr::every($id, static fn ($value) => is_int($value))) {
            throw new InvalidArgumentException('Post id must be an integer or a list of integers.');
        }

        return $this->whereNotIn('post', $id);
    }

    /**
     * Set specific post slug to retrieve.
     */
    public function whereName(string $name): self
    {
        return $this->set('name', $name);
    }

    /**
     * Set specific post slug to retrieve.
     */
    public function wherePostName(string $name): self
    {
        return $this->whereName($name);
    }

    /**
     * Alias to whereName() method
     *
     * @see whereName
     */
    public function wherePostSlug(string $name): self
    {
        return $this->whereName($name);
    }

    /** @param string|string[] $name */
    public function wherePostNameIn($name): self
    {
        $name = is_array($name) ? $name : func_get_args();

        if (! Arr::every($name, static fn ($value) => is_string($value))) {
            throw new InvalidArgumentException('Post name must be a string or a list of strings.');
        }

        return $this->whereIn('post_name', $name);
    }

    /**
     * Alias to wherePostNameIn() method
     *
     * @see wherePostNameIn
     *
     * @param string|string[] $name
     */
    public function wherePostSlugIn($name): self
    {
        $name = is_array($name) ? $name : func_get_args();

        if (! Arr::every($name, static fn ($value) => is_string($value))) {
            throw new InvalidArgumentException('Post slug must be a string or a list of strings.');
        }

        return $this->wherePostNameIn($name);
    }

    /**
     * Set specific page ID to retrieve.
     */
    public function wherePageID(int $id): self
    {
        return $this->set('page_id', $id);
    }

    /**
     * Set specific page slug to retrieve.
     */
    public function wherePageName(string $slug): self
    {
        return $this->set('pagename', $slug);
    }

    /**
     * Set specific page slug to retrieve.
     */
    public function wherePage(string $slug): self
    {
        return $this->wherePageName($slug);
    }

    /**
     * Alias to wherePageName() method
     *
     * @see wherePageName
     */
    public function wherePageSlug(string $slug): self
    {
        return $this->wherePageName($slug);
    }

    /**
     * Limit to children of specific post.
     */
    public function whereParent(int $id): self
    {
        return $this->set('post_parent', $id);
    }

    /**
     * Specify posts whose parent is in an array.
     *
     * @param int|int[] $id
     */
    public function whereParentIn($id): self
    {
        $id = is_array($id) ? $id : func_get_args();

        if (! Arr::every($id, static fn ($value) => is_int($value))) {
            throw new InvalidArgumentException('Post parent ID must be an int or a list of integers.');
        }

        return $this->whereIn('post_parent', $id);
    }

    /**
     * Specify posts whose parent is not in an array.
     *
     * @param int|int[] $id
     */
    public function whereParentNotIn($id): self
    {
        $id = is_array($id) ? $id : func_get_args();

        if (! Arr::every($id, static fn ($value) => is_int($value))) {
            throw new InvalidArgumentException('Excluded post parent ID must be an int or a list of integers.');
        }

        return $this->whereNotIn('post_parent', $id);
    }

    /**
     * Display only pages
     */
    public function whereIsPage(): self
    {
        return $this->wherePostType('page');
    }

    /**
     * -----------------------------------------------------
     * Pagination Parameters
     * -----------------------------------------------------
     */

    /**
     * Paginate the results
     *
     * @param int $perPage The maximum returned number of results
     * @param int $paged   Defines the page of results to return.
     */
    public function paginate(int $perPage, int $paged = 1): self
    {
        $this->postsPerPage($perPage);
        $this->paged($paged);

        return $this;
    }

    /**
     * Show all posts or use pagination. Default value is ‘false’, use paging.
     */
    public function nopaging(bool $nopaging = true): self
    {
        return $this->set('nopaging', $nopaging);
    }

    /**
     * Number of post to show per page.
     *
     * Use 'posts_per_page'=>-1 to show all posts (the 'offset' parameter is ignored
     * with a -1 value). Set the ‘paged’ parameter if pagination is off after using
     * this parameter.
     *
     * Note: if the query is in a feed, WordPress overwrites this parameter with the
     * stored ‘posts_per_rss’ option. To reimpose the limit, try using the ‘post_limits’
     * filter, or filter ‘pre_option_posts_per_rss’ and return -1
     */
    public function postsPerPage(int $count): self
    {
        return $this->set('posts_per_page', $count);
    }

    /**
     * Alias to postsPerPage() method.
     *
     * @see postsPerPage
     */
    public function limit(int $count): self
    {
        return $this->postsPerPage($count);
    }

    /**
     * Number of posts to show per page – on archive pages only.
     *
     * Over-rides posts_per_page and show posts on pages where is_archive() or is_search()
     * would be true.
     */
    public function postsPerArchivePage(int $count): self
    {
        return $this->set('posts_per_archive_page', $count);
    }

    /**
     * Number of post to displace or pass over.
     *
     * Warning: Setting the offset parameter overrides/ignores the paged parameter and
     * breaks pagination. The 'offset' parameter is ignored when 'posts_per_page'=>-1
     * (show all posts) is used.
     */
    public function offset(int $count): self
    {
        return $this->set('offset', $count);
    }

    /**
     * Number of post to skip.
     *
     * @see offset
     */
    public function skip(int $count): self
    {
        return $this->offset($count);
    }

    /**
     * Number of page.
     *
     * Show the posts that would normally show up just on page X when using the
     * “Older Entries” link.
     */
    public function paged(int $count): self
    {
        return $this->set('paged', $count);
    }

    /**
     * Number of page for a static front page.
     *
     * Show the posts that would normally show up just on page X of a Static Front Page.
     */
    public function page(int $count): self
    {
        return $this->set('page', $count);
    }

    /**
     * Display sticky posts only.
     */
    public function whereSticky(bool $sticky = true): self
    {
        $ids = get_option('sticky_posts');

        if ($sticky) {
            return $this->wherePostIn($ids);
        }

        return $this->wherePostNotIn($ids);
    }

    /**
     * Exclude sticky posts.
     */
    public function excludeStickyPosts(): self
    {
        return $this->whereSticky(false);
    }

    /**
     * Ignore post stickiness
     *
     * @param bool $ignore false (default): move sticky posts to the start of the set.
     *                     true: do not move sticky posts to the start of the set.
     */
    public function ignoreStickyPosts(bool $ignore = true): self
    {
        return $this->set('ignore_sticky_posts', $ignore);
    }

    /**
     * Alias to ignoreStickyPosts() method.
     *
     * @see ignoreStickyPosts
     */
    public function ignoreStickiness(): self
    {
        return $this->ignoreStickyPosts();
    }

    /**
     * -----------------------------------------------------
     * Order & Orderby Parameters
     * -----------------------------------------------------
     */

    /**
     * Designates the ascending or descending order of the ‘orderby‘ parameter.
     *
     * Defaults to ‘DESC’. An array can be used for multiple order/orderby sets.
     */
    public function order(string $order): self
    {
        return $this->set('order', strtoupper($order));
    }

    /**
     * Ascending order from lowest to highest values.
     */
    public function orderAsc(): self
    {
        return $this->order('asc');
    }

    /**
     * Descending order from highest to lowest values.
     */
    public function orderDesc(): self
    {
        return $this->order('desc');
    }

    /**
     * Sort retrieved posts by parameter.
     *
     * Defaults to ‘date (post_date)’. One or more options can be passed.
     *
     * @param string|array<string, mixed> $orderby
     */
    public function orderBy($orderby, string $order = 'desc'): self
    {
        $this->set('orderby', $orderby);
        $this->order($order);

        return $this;
    }

    /**
     * Order by post ID.
     */
    public function orderByID(string $order = 'desc'): self
    {
        $this->orderBy('ID');
        $this->order($order);

        return $this;
    }

    /**
     * Order by post author.
     */
    public function orderByAuthor(string $order = 'desc'): self
    {
        $this->orderBy('author');
        $this->order($order);

        return $this;
    }

    /**
     * Order by post title.
     */
    public function orderByTitle(string $order = 'desc'): self
    {
        $this->orderBy('title');
        $this->order($order);

        return $this;
    }

    /**
     * Order by post name.
     */
    public function orderByName(string $order = 'desc'): self
    {
        $this->orderBy('name');
        $this->order($order);

        return $this;
    }

    /**
     * Order by post slug.
     *
     * Alias to orderByName() method.
     */
    public function orderBySlug(string $order = 'desc'): self
    {
        return $this->orderByName($order);
    }

    /**
     * Order by post type.
     */
    public function orderByType(string $order = 'desc'): self
    {
        $this->orderBy('type');
        $this->order($order);

        return $this;
    }

    /**
     * Order by post date.
     */
    public function orderByDate(string $order = 'desc'): self
    {
        $this->orderBy('date');
        $this->order($order);

        return $this;
    }

    /**
     * Order by post date modified.
     */
    public function orderByDateModified(string $order = 'desc'): self
    {
        $this->orderBy('modified');
        $this->order($order);

        return $this;
    }

    /**
     * Order by post/page parent id.
     */
    public function orderByParent(string $order = 'desc'): self
    {
        $this->orderBy('parent');
        $this->order($order);

        return $this;
    }

    /**
     * Order posts randomly.
     */
    public function orderRandom(): self
    {
        return $this->orderBy('rand');
    }

    /**
     * Order by number of comments.
     */
    public function orderByCommentCount(string $order = 'desc'): self
    {
        $this->orderBy('comment_count');
        $this->order($order);

        return $this;
    }

    /**
     * Order by search terms.
     *
     * Checked in the following order: First, whether the entire sentence is matched.
     * Second, if all the search terms are within the titles. Third, if any of the
     * search terms appear in the titles. And, fourth, if the full sentence appears
     * in the contents.
     */
    public function orderByRelevance(string $order = 'desc'): self
    {
        $this->orderBy('relevance');
        $this->order($order);

        return $this;
    }

    /**
     * Order by Page Order.
     *
     * Used most often for pages (Order field in the Edit Page Attributes box) and for
     * attachments (the integer fields in the Insert / Upload Media Gallery dialog),
     * but could be used for any post type with distinct ‘menu_order‘ values (they all
     * default to 0).
     */
    public function orderByMenuOrder(string $order = 'desc'): self
    {
        $this->orderBy('menu_order');
        $this->order($order);

        return $this;
    }

    public function orderByMetaValueNum(string $key, string $order = 'ASC'): self
    {
        $this->orderBy('meta_value_num', $order);
        $this->set('meta_key', $key);

        return $this;
    }

    /**
     * -----------------------------------------------------
     * Date Parameters
     * -----------------------------------------------------
     * Show posts associated with a certain time and date
     * period.
     */

    /**
     * Show posts associated with a certain year.
     *
     * @param int $year 4 digit year (e.g. 2022).
     */
    public function whereYear(int $year): self
    {
        if (strlen((string) $year) !== 4) {
            throw new InvalidArgumentException('Year parameter must be in a 4 digit format.');
        }

        return $this->where('year', $year);
    }

    /**
     * Show posts associated with a certain month number.
     *
     * @param int $month Month number (from 1 to 12).
     */
    public function whereMonthNumber(int $month): self
    {
        if ($month > 12 || $month < 1) {
            throw new InvalidArgumentException('Month number parameter must be a number from 1-12.');
        }

        return $this->where('monthnum', $month);
    }

    /**
     * Show posts associated with a certain week of the year.
     *
     * @param int $week Week of the year (from 0 to 53). Uses MySQL WEEK command.
     *                  The mode is dependent on the “start_of_week” option.
     */
    public function whereWeek(int $week): self
    {
        if ($week > 53 || $week < 0) {
            throw new InvalidArgumentException('Week number parameter must be a number from 0 to 53.');
        }

        return $this->where('w', $week);
    }

    /**
     * Show posts associated with a certain day of the month.
     *
     * @param int $day Day of the month (from 1 to 31).
     */
    public function whereDay(int $day): self
    {
        if ($day > 31 || $day < 1) {
            throw new InvalidArgumentException('Day number parameter must be a number from 1 to 31.');
        }

        return $this->where('day', $day);
    }

    /**
     * Show posts associated with a certain hour of the day.
     *
     * @param int $hour Hour (from 0 to 23).
     */
    public function whereHour(int $hour): self
    {
        if ($hour > 23 || $hour < 0) {
            throw new InvalidArgumentException('Hour number parameter must be a number from 0 to 23');
        }

        return $this->where('hour', $hour);
    }

    /**
     * Show posts associated with a certain minute.
     *
     * @param int $minute Minute (from 0 to 60).
     */
    public function whereMinute(int $minute): self
    {
        if ($minute > 60 || $minute < 0) {
            throw new InvalidArgumentException('Minute number parameter must be a number from 0 to 60');
        }

        return $this->where('minute', $minute);
    }

    /**
     * Show posts associated with a certain Second.
     *
     * @param int $minute Second (from 0 to 60).
     */
    public function whereSecond(int $minute): self
    {
        if ($minute > 60 || $minute < 0) {
            throw new InvalidArgumentException('Second number parameter must be a number from 0 to 60');
        }

        return $this->where('second', $minute);
    }

    /**
     * Show posts associated with a certain Second.
     *
     * @param int $yearMonth YearMonth (For e.g.: 201307).
     */
    public function whereYearMonth(int $yearMonth): self
    {
        if (strlen((string) $yearMonth) !== 6) {
            throw new InvalidArgumentException('YearMonth parameter must be in a 6 digit format (For e.g.: 201307).');
        }

        return $this->where('m', $yearMonth);
    }

    /**
     * -----------------------------------------------------
     * Custom Field (post meta) Parameters
     * -----------------------------------------------------
     * Show posts associated with a certain custom field.
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

    /**
     * Display posts with a custom field value of zero (0), regardless of the custom field key.
     */
    public function whereMetaZeroValue(): self
    {
        $this->set('meta_value', '_wp_zero_value');

        return $this;
    }

    /**
     * -----------------------------------------------------
     * Permission Parameters
     * -----------------------------------------------------
     */

    /**
     * Show posts if user has the appropriate capability
     */
    public function userPermission(string $perm): self
    {
        $this->set('perm', $perm);

        return $this;
    }

    /**
     * -----------------------------------------------------
     * Caching Parameters
     * -----------------------------------------------------
     * Stop the data retrieved from being added to the cache.
     */

    /**
     * Stop the data retrieved from being added to the cache.
     */
    public function cacheResults(bool $cache = true): self
    {
        $this->set('cache_results', $cache);

        return $this;
    }

    /**
     * Whether to add the retrieved post meta information to the cache
     */
    public function updatePostMetaCache(bool $update = true): self
    {
        $this->set('update_post_meta_cache', $update);

        return $this;
    }

    /**
     * Whether to add the retrieved post term information to the cache
     */
    public function updatePostTermCache(bool $update = true): self
    {
        $this->set('update_post_term_cache', $update);

        return $this;
    }

    /**
     * -----------------------------------------------------
     * Taxonomy Parameters
     * -----------------------------------------------------
     * Show posts associated with certain taxonomy.
     */

    /**
     * @param string|Closure(TaxQuery): void $tax
     * @param string|int|array<string|int>   $terms
     */
    public function whereTax(
        $tax,
        ?string $field = null,
        $terms = null,
        string $operator = 'in',
        bool $children = true
    ): self {
        if (isset($this->query['tax_query']) && ! isset($this->query['tax_query']['relation'])) {
            $this->query['tax_query']['relation'] = 'AND';
        }

        if (! $tax instanceof Closure) {
            $this->query['tax_query'][] = [
                'taxonomy'         => $tax,
                'field'            => $field,
                'terms'            => $terms,
                'include_children' => $children,
                'operator'         => Helpers::parseCompareOperator($operator),
            ];

            return $this;
        }

        $callback = $tax;
        $taxQuery = new TaxQuery();
        call_user_func($callback, $taxQuery);

        $this->query['tax_query'][] = $taxQuery->getQueryArgs();

        return $this;
    }

    /**
     * @param string|Closure(TaxQuery): void $tax
     * @param string|int|array<string|int>   $terms
     * @param bool                           $includeChildren Whether to include children
     */
    public function andWhereTax(
        $tax,
        ?string $field = null,
        $terms = null,
        string $operator = 'in',
        bool $includeChildren = true
    ): self {
        $this->whereTax($tax, $field, $terms, $operator, $includeChildren);

        return $this;
    }

    /**
     * @param string|Closure(TaxQuery): void $tax
     * @param string|int|array<string|int>   $terms
     */
    public function orWhereTax(
        $tax,
        ?string $field = null,
        $terms = null,
        string $operator = 'in',
        bool $includeChildren = true
    ): self {
        $this->whereTax($tax, $field, $terms, $operator, $includeChildren);
        $this->query['tax_query']['relation'] = 'OR';

        return $this;
    }

    /** @param int|int[] $id */
    public function whereTermID(string $tax, $id, string $operator = 'in', bool $includeChildren = true): self
    {
        $this->whereTax($tax, 'term_id', $id, $operator, $includeChildren);

        return $this;
    }

    /** @param int|int[] $id */
    public function orWhereTermID(string $tax, $id, string $operator = 'in', bool $includeChildren = true): self
    {
        $this->orWhereTax($tax, 'term_id', $id, $operator, $includeChildren);

        return $this;
    }

    /** @param int|int[] $id */
    public function andWhereTermID(string $tax, $id, string $operator = 'in', bool $includeChildren = true): self
    {
        $this->andWhereTax($tax, 'term_id', $id, $operator, $includeChildren);

        return $this;
    }

    /** @param string|string[] $name */
    public function whereTermSlug(string $tax, $name, string $operator = 'in', bool $includeChildren = true): self
    {
        $this->whereTax($tax, 'slug', $name, $operator, $includeChildren);

        return $this;
    }

    /** @param string|string[] $name */
    public function orWhereTermSlug(string $tax, $name, string $operator = 'in', bool $includeChildren = true): self
    {
        $this->orWhereTax($tax, 'slug', $name, $operator, $includeChildren);

        return $this;
    }

    /** @param string|string[] $name */
    public function andWhereTermSlug(string $tax, $name, string $operator = 'in', bool $includeChildren = true): self
    {
        $this->andWhereTax($tax, 'slug', $name, $operator, $includeChildren);

        return $this;
    }

    /** @param string|string[] $name */
    public function whereTermName(string $tax, $name, string $operator = 'in', bool $includeChildren = true): self
    {
        $this->whereTax($tax, 'name', $name, $operator, $includeChildren);

        return $this;
    }

    /** @param string|string[] $name */
    public function orWhereTermName(string $tax, $name, string $operator = 'in', bool $includeChildren = true): self
    {
        $this->orWhereTax($tax, 'name', $name, $operator, $includeChildren);

        return $this;
    }

    /** @param string|string[] $name */
    public function andWhereTermName(string $tax, $name, string $operator = 'in', bool $includeChildren = true): self
    {
        $this->andWhereTax($tax, 'name', $name, $operator, $includeChildren);

        return $this;
    }

    /** @param int|int[] $id */
    public function whereTermTaxID(string $tax, $id, string $operator = 'in', bool $includeChildren = true): self
    {
        $this->whereTax($tax, 'term_taxonomy_id', $id, $operator, $includeChildren);

        return $this;
    }

    /** @param int|int[] $id */
    public function orWhereTermTaxID(string $tax, $id, string $operator = 'in', bool $includeChildren = true): self
    {
        $this->orWhereTax($tax, 'term_taxonomy_id', $id, $operator, $includeChildren);

        return $this;
    }

    /** @param int|int[] $id */
    public function andWhereTermTaxID(string $tax, $id, string $operator = 'in', bool $includeChildren = true): self
    {
        $this->andWhereTax($tax, 'term_taxonomy_id', $id, $operator, $includeChildren);

        return $this;
    }

    /**
     * -----------------------------------------------------
     * Mime Type Parameters
     * -----------------------------------------------------
     * Used with the attachments post type.
     */

    /**
     * Filter results by attachment mime type
     *
     * @param string|string[] $mimes
     */
    public function whereMimeType($mimes): self
    {
        $mimes = is_array($mimes) || func_num_args() === 1 ? $mimes : func_get_args();

        if (
            is_array($mimes) && ! Arr::every($mimes, static fn ($value) => is_string($value))
            || ! is_array($mimes) && ! is_string($mimes) // @phpstan-ignore-line
        ) {
            throw new InvalidArgumentException('Mime type must be a string or a list of strings.');
        }

        $this->set('post_mime_type', $mimes);

        return $this;
    }

    public function whereNotImage(): self
    {
        $mimes = array_filter(
            get_allowed_mime_types(),
            static fn ($mime) => strncmp($mime, 'image', strlen('image')) !== 0
        );

        return $this->whereMimeType($mimes);
    }

    public function excludeImages(): self
    {
        return $this->whereNotImage();
    }

    public function fields(string $fields): self
    {
        if (! in_array($fields, ['ids', 'all'])) {
            throw new InvalidArgumentException(sprintf(
                'Fields argument "%s" is not supported. Supported: "all" or "ids".',
                $fields
            ));
        }

        $this->set('fields', $fields);

        return $this;
    }

    public function returnAllFields(): self
    {
        return $this->fields('all');
    }

    public function returnIDs(): self
    {
        return $this->fields('ids');
    }

    public function suppressFilters(bool $suppress = true): self
    {
        $this->set('suppress_filters', $suppress);

        return $this;
    }

    /**
     * Retrieves all the posts matching the query.
     *
     * @param string|null $returnType Class name of the expected object type to return.
     *                                If not set, will return a collection of WP_Post
     *                                objects or post IDs if query set to return IDs.
     */
    public function get(?string $returnType = null): Collection
    {
        $posts = get_posts($this->getQueryArgs());
        $posts = collect($posts);

        if (empty($returnType)) {
            return $posts;
        }

        try {
            new ReflectionClass($returnType);
        } catch (ReflectionException $e) {
            throw new RuntimeException($e->getMessage());
        }

        return $posts->map(static fn ($post) => new $returnType($post));
    }

    /**
     * Get all matching results
     *
     * @param string|null $returnType Class name of the expected object type to return.
     *                                If not set, will return a collection of WP_Post
     *                                objects or post IDs if query set to return IDs.
     */
    public function all(?string $returnType = null): Collection
    {
        return $this->limit(-1)->get($returnType);
    }

    /**
     * Retrieve the first result matching the query
     *
     * @param string|null $returnType Class name of the expected object type to return.
     *
     * @return int|WP_Post|object Post ID if query set to return IDs, custom object
     *                            if return type is not empty. Otherwise, instance
     *                            of WP_Post.
     *
     * @throws ObjectNotFoundException If no matching results were found.
     */
    public function firstOfAll(?string $returnType = null)
    {
        $posts = $this->orderAsc()->limit(1)->get($returnType);

        if ($posts->isEmpty()) {
            throw new ObjectNotFoundException('No post found');
        }

        return $posts[0];
    }

    /**
     * Retrieve the last result matching the query
     *
     * @param string|null $returnType Class name of the expected object type to return.
     *
     * @return int|WP_Post|object Post ID if query set to return IDs, custom object
     *                            if return type is not empty. Otherwise, instance
     *                            of WP_Post.
     *
     * @throws ObjectNotFoundException If no matching results were found.
     */
    public function lastOfAll(?string $returnType = null)
    {
        $posts = $this->orderDesc()->limit(1)->get($returnType);

        if ($posts->isEmpty()) {
            throw new ObjectNotFoundException('No post found');
        }

        return $posts[0];
    }
}
