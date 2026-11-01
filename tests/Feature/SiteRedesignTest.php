<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Subscribers\Pages\ManageSubscribers;
use App\Models\Event;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteRedesignTest extends TestCase
{
    use RefreshDatabase;

    private function homepage(array $blocks, array $attributes = []): Page
    {
        return Page::factory()->create([...['title' => 'Home', 'slug' => 'home', 'is_homepage' => true, 'blocks' => $blocks], ...$attributes]);
    }

    public function test_published_homepage_page_renders_its_blocks_at_the_root(): void
    {
        Post::factory()->create(['title' => 'A fresh insight', 'status' => 'published']);
        Event::factory()->create(['title' => 'Community workshop']);

        $this->homepage([
            ['type' => 'hero', 'data' => ['heading' => 'Advancing good work', 'text' => 'We help people.', 'button_label' => 'Learn more', 'button_url' => '/about']],
            ['type' => 'cards', 'data' => ['heading' => 'What we do', 'columns' => '3', 'cards' => [['title' => 'Research', 'text' => 'Studies', 'url' => '/research']]]],
            ['type' => 'stats', 'data' => ['items' => [['value' => '25,000+', 'label' => 'people trained']]]],
            ['type' => 'latest_posts', 'data' => ['count' => '4']],
            ['type' => 'upcoming_events', 'data' => ['count' => '3']],
            ['type' => 'cta_banner', 'data' => ['heading' => 'Join us', 'button_label' => 'Get in touch', 'button_url' => '/contact']],
            ['type' => 'newsletter', 'data' => []],
        ]);

        $this->get('/')->assertOk()
            ->assertSeeInOrder(['Advancing good work', 'What we do', '25,000+', 'A fresh insight', 'Community workshop', 'Join us', 'Stay in the loop'])
            ->assertSee('href="/about"', false)
            ->assertSee('href="/contact"', false);
    }

    public function test_draft_homepage_falls_back_to_latest_insights(): void
    {
        $this->homepage([['type' => 'hero', 'data' => ['heading' => 'Secret draft headline']]], ['status' => 'draft']);
        Post::factory()->create(['title' => 'Visible insight', 'status' => 'published']);

        $this->get('/')->assertOk()->assertSee('Visible insight')->assertDontSee('Secret draft headline');
    }

    public function test_only_one_page_can_be_the_homepage_and_its_slug_redirects_to_root(): void
    {
        $first = $this->homepage([]);
        $second = Page::factory()->create(['slug' => 'new-home', 'is_homepage' => true]);

        $this->assertFalse($first->fresh()->is_homepage);
        $this->assertTrue($second->fresh()->is_homepage);
        $this->get('/new-home')->assertRedirect('/')->assertStatus(301);
    }

    public function test_blocks_never_render_unsafe_links_or_html(): void
    {
        $this->homepage([
            ['type' => 'hero', 'data' => ['heading' => 'Hi', 'button_label' => 'Click', 'button_url' => 'javascript:alert(1)']],
            ['type' => 'text', 'data' => ['content' => '<p>Safe</p><script>alert(2)</script>']],
            ['type' => 'cards', 'data' => ['cards' => [['title' => 'Card', 'url' => 'javascript:alert(3)']]]],
            ['type' => 'not_a_real_block', 'data' => ['heading' => 'Should not render']],
        ]);

        $this->get('/')->assertOk()
            ->assertSee('Safe')
            ->assertDontSee('javascript:', false)
            ->assertDontSee('<script>alert', false)
            ->assertDontSee('Should not render');
    }

    public function test_menu_shows_parent_pages_with_dropdown_children(): void
    {
        $about = Page::factory()->inNavigation(1)->create(['title' => 'About us', 'slug' => 'about']);
        Page::factory()->create(['title' => 'Our team', 'slug' => 'team', 'parent_id' => $about->id]);
        Page::factory()->draft()->create(['title' => 'Hidden child', 'slug' => 'hidden-child', 'parent_id' => $about->id]);

        $this->get('/contact')->assertOk()
            ->assertSee('aria-haspopup="true"', false)
            ->assertSeeInOrder(['About us', 'Our team'])
            ->assertSee('href="'.url('/team').'"', false)
            ->assertDontSee('Hidden child');
    }

    public function test_child_page_shows_breadcrumb_to_its_parent(): void
    {
        $about = Page::factory()->create(['title' => 'About us', 'slug' => 'about']);
        Page::factory()->create(['title' => 'Our team', 'slug' => 'team', 'parent_id' => $about->id]);

        $this->get('/team')->assertOk()->assertSee('aria-label="Breadcrumb"', false)->assertSee('href="'.url('/about').'"', false);
    }

    public function test_old_post_addresses_redirect_to_insights(): void
    {
        $post = Post::factory()->create(['slug' => 'my-story', 'status' => 'published']);

        $this->get('/posts')->assertRedirect('/insights')->assertStatus(301);
        $this->get('/posts/my-story')->assertRedirect(route('posts.show', 'my-story'))->assertStatus(301);
        $this->get('/insights/my-story')->assertOk()->assertSee($post->title);
    }

    public function test_events_list_upcoming_and_past_separately(): void
    {
        Event::factory()->create(['title' => 'Future meetup']);
        Event::factory()->past()->create(['title' => 'Last year conference']);
        Event::factory()->draft()->create(['title' => 'Unpublished event']);

        $this->get('/events')->assertOk()->assertSee('Future meetup')
            ->assertDontSee('Last year conference')->assertDontSee('Unpublished event');
        $this->get('/events?when=past')->assertOk()->assertSee('Last year conference')->assertDontSee('Future meetup');
    }

    public function test_event_page_shows_details_and_safe_registration_link(): void
    {
        Event::factory()->create([
            'title' => 'Open day', 'slug' => 'open-day', 'location' => 'Kampala',
            'registration_url' => 'https://tickets.example.org/open-day', 'body' => '<p>Join us</p><script>x()</script>',
        ]);
        Event::factory()->create(['slug' => 'bad-link', 'registration_url' => 'javascript:alert(1)']);

        $this->get('/events/open-day')->assertOk()->assertSee('Kampala')->assertSee('Join us')
            ->assertSee('https://tickets.example.org/open-day')->assertDontSee('<script>x()', false);
        $this->get('/events/bad-link')->assertOk()->assertDontSee('javascript:', false)->assertDontSee('>Register<', false);
    }

    public function test_newsletter_signup_saves_subscriber_once_and_returns_to_the_page(): void
    {
        $this->from('/events')->post('/newsletter', ['email' => 'Reader@Example.org'])
            ->assertRedirect(url('/events').'#newsletter')->assertSessionHas('newsletter_status');
        $this->from('/events')->post('/newsletter', ['email' => 'reader@example.org'])->assertSessionHas('newsletter_status');

        $this->assertSame(1, Subscriber::query()->count());
        $this->assertDatabaseHas('subscribers', ['email' => 'reader@example.org']);
    }

    public function test_newsletter_rejects_invalid_email_honeypot_and_offsite_redirects(): void
    {
        $this->from('/contact')->post('/newsletter', ['email' => 'nope'])->assertSessionHasErrorsIn('newsletter', ['email']);
        $this->post('/newsletter', ['email' => 'bot@spam.test', 'website' => 'x'])->assertSessionHas('newsletter_status');
        $this->post('/newsletter', ['email' => 'ok@example.org'], ['referer' => 'https://evil.example.com/page'])
            ->assertRedirect(route('home').'#newsletter');

        $this->assertDatabaseMissing('subscribers', ['email' => 'bot@spam.test']);
        $this->assertDatabaseHas('subscribers', ['email' => 'ok@example.org']);
    }

    public function test_brand_colours_from_settings_are_applied_and_invalid_ones_ignored(): void
    {
        SiteSetting::current()->update(['primary_color' => '#0b5fff', 'accent_color' => 'red;}body{display:none']);

        $this->get('/contact')->assertOk()
            ->assertSee('--brand: #0B5FFF', false)
            ->assertSee('--brand-fg: #FFFFFF', false)
            ->assertSee('--ink: '.SiteSetting::DEFAULT_ACCENT, false)
            ->assertDontSee('display:none', false);
    }

    public function test_readable_text_colour_is_chosen_for_light_and_dark_brands(): void
    {
        $this->assertSame('#111111', SiteSetting::readableTextOn('#FFD60A'));
        $this->assertSame('#FFFFFF', SiteSetting::readableTextOn('#1C1917'));
    }

    public function test_sitemap_includes_events_and_skips_homepage_slug(): void
    {
        Event::factory()->create(['slug' => 'big-event']);
        $this->homepage([]);

        $this->get('/sitemap.xml')->assertOk()
            ->assertSee(route('events.show', 'big-event'))
            ->assertDontSee(url('/home'));
    }

    public function test_admin_can_build_a_page_from_blocks(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create()->assignRole('Admin'));

        Livewire::test(CreatePage::class)
            ->fillForm([
                'title' => 'Services', 'slug' => 'services', 'status' => 'published',
                'blocks' => [
                    ['type' => 'cta_banner', 'data' => ['heading' => 'Talk to us', 'button_label' => 'Contact', 'button_url' => '/contact']],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->get('/services')->assertOk()->assertSee('Talk to us');
    }

    public function test_events_and_subscribers_admin_access_follows_roles(): void
    {
        $this->seed();
        $admin = User::factory()->create()->assignRole('Admin');
        $editor = User::factory()->create()->assignRole('Editor');

        $this->actingAs($admin)->get('/admin/events')->assertOk();
        $this->actingAs($admin)->get('/admin/subscribers')->assertOk();
        $this->actingAs($editor)->get('/admin/events')->assertOk();
        $this->actingAs($editor)->get('/admin/subscribers')->assertForbidden();
    }

    public function test_subscribers_can_be_downloaded_as_csv(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create()->assignRole('Admin'));
        Subscriber::query()->create(['email' => 'a@example.org', 'name' => '=HYPERLINK("x")']);

        Livewire::test(ManageSubscribers::class)
            ->callAction('exportCsv')
            ->assertFileDownloaded('subscribers-'.now()->format('Y-m-d').'.csv', content: "email,name,subscribed_at\na@example.org,\"'=HYPERLINK(\"\"x\"\")\",\"".Subscriber::query()->first()->created_at->toDateTimeString()."\"\n");
    }

    public function test_starter_homepage_is_seeded_as_a_draft_once(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(1, Page::query()->where('is_homepage', true)->count());
        $this->assertSame('draft', Page::query()->where('is_homepage', true)->value('status'));
    }
}
