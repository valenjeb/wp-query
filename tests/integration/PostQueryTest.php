<?php

declare(strict_types=1);

namespace Devly\WP\Query\Tests\Integration;

use Devly\WP\Query\PostQuery;
use WP_Post;
use WP_UnitTestCase;

class PostQueryTest extends WP_UnitTestCase
{
    /** @var int[] */
    protected array $posts;
    /** @var array|WP_Post|null */
    protected $post;

    public function set_up(): void
    {
        parent::set_up();

        $this->posts = $this->factory()->post->create_many(4);
        $this->post  = get_post($this->posts[0]);

        wp_update_post(['ID' => $this->post->ID, 'post_password' => 1234]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->posts);
    }

    public function testGetAllPosts(): void
    {
        $posts = PostQuery::create()->all();

        $this->assertEquals(4, $posts->count());
    }

    public function testGetPostById(): void
    {
        $post = PostQuery::create()->wherePostID($this->post->ID)->get();

        $this->assertEquals($this->post->ID, $post[0]->ID);
    }

    public function testGetPostBySlug(): void
    {
        $post = PostQuery::create()->wherePostSlug($this->post->post_name)->get();

        $this->assertEquals($this->post->ID, $post[0]->ID);
    }

    public function testGetPostWherePassword(): void
    {
        $post = PostQuery::create()->wherePassword(true)->get();

        $this->assertEquals($this->post->ID, $post[0]->ID);
    }

    public function testGetPostWherePasswordWithValue(): void
    {
        $post = PostQuery::create()->wherePassword(1234)->get();

        $this->assertEquals(1, $post->count());
        $this->assertEquals($this->post->ID, $post[0]->ID);
    }
}
