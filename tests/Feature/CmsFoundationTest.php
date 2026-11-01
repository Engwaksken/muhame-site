<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CmsFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_seven_admin_roles_and_permissions(): void
    {
        $this->seed();

        $this->assertSame(7, Role::query()->count());
        $this->assertTrue(Role::findByName('Super Admin')->hasPermissionTo('posts.publish'));
        $this->assertFalse(Role::findByName('Viewer')->hasPermissionTo('posts.create'));
    }

    public function test_filament_exposes_a_login_page_and_protects_the_admin_dashboard(): void
    {
        $this->get('/admin/login')->assertOk();
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/register')->assertNotFound();
    }

    public function test_user_can_be_assigned_a_role_and_create_posts(): void
    {
        $this->seed();
        $author = User::factory()->create();
        $author->assignRole('Author');

        $this->assertTrue($author->can('create', Post::class));

        $post = Post::factory()->for($author, 'author')->create();
        $this->assertTrue($author->can('update', $post));
        $this->assertFalse($author->can('publish', $post));
    }

    public function test_contributor_cannot_update_another_authors_post(): void
    {
        $this->seed();
        $contributor = User::factory()->create();
        $contributor->assignRole('Contributor');
        $post = Post::factory()->create();

        $this->assertFalse($contributor->can('update', $post));
        $this->assertTrue($contributor->can('viewAny', Post::class));
    }

    public function test_editor_can_manage_taxonomy_while_viewer_cannot(): void
    {
        $this->seed();
        $editor = User::factory()->create();
        $editor->assignRole('Editor');
        $viewer = User::factory()->create();
        $viewer->assignRole('Viewer');

        $this->assertTrue($editor->can('create', Category::class));
        $this->assertTrue($editor->can('create', Tag::class));
        $this->assertFalse($viewer->can('create', Category::class));
        $this->assertFalse($viewer->can('create', Tag::class));
    }

    public function test_editor_can_open_content_resources_and_viewer_cannot_manage_taxonomy(): void
    {
        $this->seed();
        $editor = User::factory()->create();
        $editor->assignRole('Editor');

        $this->actingAs($editor)->get('/admin/posts')->assertOk();
        $this->actingAs($editor)->get('/admin/categories')->assertOk();
        $this->actingAs($editor)->get('/admin/tags')->assertOk();

        $viewer = User::factory()->create();
        $viewer->assignRole('Viewer');

        $this->actingAs($viewer)->get('/admin/categories')->assertForbidden();
        $this->actingAs($viewer)->get('/admin/tags')->assertForbidden();
    }

    public function test_posts_support_categories_tags_and_safe_category_deletion(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $post = Post::factory()->for($category)->create();
        $post->tags()->attach($tag);

        $this->assertTrue($post->tags->contains($tag));
        $category->delete();
        $tag->delete();

        $this->assertNull($post->fresh()->category_id);
        $this->assertDatabaseMissing('post_tag', ['post_id' => $post->id, 'tag_id' => $tag->id]);
    }

    public function test_publishing_a_post_sets_published_at(): void
    {
        $post = Post::factory()->create(['status' => 'draft', 'published_at' => null]);

        $post->update(['status' => 'published']);

        $this->assertNotNull($post->fresh()->published_at);
    }

    public function test_public_pages_only_show_published_posts(): void
    {
        $published = Post::factory()->published()->create(['title' => 'Public story']);
        Post::factory()->create(['title' => 'Private draft']);
        Post::factory()->published()->create(['title' => 'Future story', 'published_at' => now()->addDay()]);

        $this->get('/')->assertOk()->assertSee('Public story')->assertDontSee('Private draft')->assertDontSee('Future story');
        $this->get('/insights/'.$published->slug)->assertOk()->assertSee('Public story');
        $this->get('/insights/private-draft')->assertNotFound();
    }

    public function test_public_content_filters_by_category_and_tag_and_sanitizes_article_html(): void
    {
        $category = Category::factory()->create(['name' => 'News', 'slug' => 'news']);
        $tag = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
        $post = Post::factory()->published()->for($category)->create([
            'title' => 'Tagged story',
            'slug' => 'tagged-story',
            'body' => '<p>Safe <strong>content</strong></p><script>alert("bad")</script>',
        ]);
        $post->tags()->attach($tag);

        $this->get('/categories/news')->assertOk()->assertSee('Tagged story');
        $this->get('/tags/laravel')->assertOk()->assertSee('Tagged story');
        $this->get('/insights/tagged-story')->assertOk()->assertSee('<strong>content</strong>', false)->assertDontSee('<script>', false)->assertDontSee('alert("bad")');
    }

    public function test_search_only_returns_published_content_and_sitemap_excludes_noindex_posts(): void
    {
        $visible = Post::factory()->published()->create(['title' => 'Searchable lighthouse']);
        Post::factory()->published()->create(['title' => 'Hidden lighthouse', 'noindex' => true]);
        Post::factory()->create(['title' => 'Draft lighthouse']);
        Post::factory()->published()->create(['title' => 'Future lighthouse', 'published_at' => now()->addDay()]);

        $this->get('/search?q=lighthouse')
            ->assertOk()
            ->assertSee('Searchable lighthouse')
            ->assertSee('Hidden lighthouse')
            ->assertDontSee('Draft lighthouse')
            ->assertDontSee('Future lighthouse')
            ->assertSee('noindex,follow');

        $sitemap = $this->get('/sitemap.xml')->assertOk();
        $sitemap->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('posts.show', $visible->slug), false)
            ->assertDontSee('hidden-lighthouse', false)
            ->assertDontSee('draft-lighthouse', false)
            ->assertDontSee('future-lighthouse', false);

        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertSee(route('sitemap'));
    }

    public function test_post_seo_metadata_is_escaped_and_noindex_is_honored(): void
    {
        $post = Post::factory()->published()->create([
            'title' => 'SEO test',
            'slug' => 'seo-test',
            'seo_title' => '<script>alert(1)</script>',
            'seo_description' => 'A custom description',
            'noindex' => true,
        ]);

        $this->get('/insights/'.$post->slug)
            ->assertOk()
            ->assertSee('A custom description')
            ->assertSee('noindex,follow')
            ->assertSee('alert(1)')
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_site_settings_are_limited_to_admin_roles(): void
    {
        $this->seed();
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $editor = User::factory()->create();
        $editor->assignRole('Editor');

        $this->assertTrue($admin->can('update', SiteSetting::current()));
        $this->assertFalse($editor->can('update', SiteSetting::current()));

        $this->actingAs($admin)->get('/admin/site-settings')->assertOk();
        $this->actingAs($editor)->get('/admin/site-settings')->assertForbidden();
    }

    public function test_public_layout_uses_site_settings_and_filters_unsafe_navigation_urls(): void
    {
        $settings = SiteSetting::current();
        $settings->update([
            'site_name' => 'Example Journal',
            'tagline' => 'Independent stories.',
            'homepage_title' => 'Made for readers',
            'navigation_links' => ['About' => '/about', 'Unsafe' => 'javascript:alert(1)'],
            'social_links' => ['Instagram' => 'https://instagram.com/example', 'Unsafe' => 'javascript:alert(1)'],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Example Journal')
            ->assertSee('Made for readers')
            ->assertSee('Independent stories.')
            ->assertSee('href="/about"', false)
            ->assertSee('Instagram')
            ->assertDontSee('javascript:alert(1)', false);
    }

    public function test_create_admin_command_seeds_roles_and_grants_panel_access(): void
    {
        $this->artisan('cms:create-admin', ['email' => 'owner@muhame.test', '--name' => 'Site Owner'])
            ->expectsQuestion('Password (at least 12 characters)', 'a-long-secure-password')
            ->assertSuccessful();

        $user = User::query()->where('email', 'owner@muhame.test')->firstOrFail();

        $this->assertTrue($user->hasRole('Super Admin'));
        $this->assertSame('Site Owner', $user->name);
        $this->actingAs($user)->get('/admin')->assertOk();
    }

    public function test_create_admin_command_rejects_short_passwords(): void
    {
        $this->artisan('cms:create-admin', ['email' => 'owner@muhame.test'])
            ->expectsQuestion('Password (at least 12 characters)', 'short')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'owner@muhame.test']);
    }

    public function test_robots_txt_is_served_dynamically_with_the_app_url(): void
    {
        $this->assertFileDoesNotExist(public_path('robots.txt'));

        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.route('sitemap'));
    }
}
