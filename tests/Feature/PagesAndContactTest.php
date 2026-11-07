<?php

namespace Tests\Feature;

use App\Filament\Resources\ContactMessages\Pages\ManageContactMessages;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\SiteSettings\Pages\ManageSiteSettings;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class PagesAndContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_pages_are_served_at_the_top_level(): void
    {
        Page::factory()->create(['title' => 'About us', 'slug' => 'about', 'body' => '<p>Our story.</p>']);

        $this->get('/about')->assertOk()->assertSee('About us')->assertSee('Our story.');
    }

    public function test_draft_and_missing_pages_return_not_found(): void
    {
        Page::factory()->draft()->create(['slug' => 'secret']);

        $this->get('/secret')->assertNotFound();
        $this->get('/does-not-exist')->assertNotFound();
    }

    public function test_public_site_does_not_link_to_the_admin_panel(): void
    {
        $this->get('/')->assertOk()->assertDontSee('/admin', false);
        $this->get('/contact')->assertOk()->assertDontSee('/admin', false);
    }

    public function test_page_routes_do_not_shadow_reserved_paths(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/up')->assertOk();
        $this->get('/contact')->assertOk()->assertSee('Contact us');
    }

    public function test_page_body_is_sanitized(): void
    {
        Page::factory()->create(['slug' => 'about', 'body' => '<p>Hello</p><script>alert(1)</script><img src=x onerror="alert(2)">']);

        $this->get('/about')->assertOk()->assertSee('Hello')
            ->assertDontSee('<script>', false)
            ->assertDontSee('onerror', false);
    }

    public function test_page_header_can_show_a_page_specific_external_button(): void
    {
        Page::factory()->create([
            'title' => 'Partners',
            'slug' => 'partners',
            'header_link_label' => 'Get your eHub license',
            'header_link_url' => 'https://app.ehub.academy',
        ]);

        $this->get('/partners')->assertOk()
            ->assertSee('Get your eHub license')
            ->assertSee('href="https://app.ehub.academy"', false)
            ->assertSee('target="_blank" rel="noopener noreferrer"', false);
    }

    public function test_page_header_button_keeps_the_logo_in_the_main_header(): void
    {
        SiteSetting::current()->update(['logo_path' => 'brand/logo.png']);

        Page::factory()->create([
            'title' => 'Partners',
            'slug' => 'partners',
            'header_link_label' => 'Get your eHub license',
            'header_link_url' => 'https://app.ehub.academy',
        ]);

        $response = $this->get('/partners')->assertOk();
        preg_match('/<header\b.*?<\/header>/s', $response->getContent(), $header);

        $this->assertNotEmpty($header);
        $this->assertStringContainsString(url('/storage/brand/logo.png'), $header[0]);
        $this->assertStringContainsString('Get your eHub license', $header[0]);
        preg_match('/data-top-header.*?data-main-menu/s', $header[0], $topHeader);
        $this->assertNotEmpty($topHeader);
        $this->assertStringContainsString(url('/storage/brand/logo.png'), $topHeader[0]);
        $this->assertStringContainsString('Get your eHub license', $topHeader[0]);
    }

    public function test_navigation_shows_published_menu_pages_in_order(): void
    {
        Page::factory()->inNavigation(2)->create(['title' => 'Services', 'slug' => 'services']);
        Page::factory()->inNavigation(1)->create(['title' => 'About', 'slug' => 'about']);
        Page::factory()->draft()->inNavigation(0)->create(['title' => 'Hidden draft', 'slug' => 'hidden-draft']);
        Page::factory()->create(['title' => 'Not in menu', 'slug' => 'not-in-menu']);

        $response = $this->get('/')->assertOk()
            ->assertSeeInOrder(['href="'.url('/about').'"', 'href="'.url('/services').'"'], false)
            ->assertDontSee('Hidden draft')
            ->assertDontSee('Not in menu');

        preg_match('/<header\b.*?<\/header>/s', $response->getContent(), $header);
        $this->assertNotEmpty($header);
        $this->assertStringNotContainsString(route('posts.index'), $header[0]);
        $this->assertStringNotContainsString(route('events.index'), $header[0]);
        $this->assertStringNotContainsString('Insights', $header[0]);
        $this->assertStringNotContainsString('Events', $header[0]);
        $this->assertStringContainsString(route('contact'), $header[0]);
        $this->assertStringContainsString('Contact us', $header[0]);
    }

    public function test_footer_lists_only_admin_selected_published_pages_in_selected_order(): void
    {
        $about = Page::factory()->create(['title' => 'About footer', 'slug' => 'about-footer']);
        $privacy = Page::factory()->create(['title' => 'Privacy footer', 'slug' => 'privacy-footer']);
        $draft = Page::factory()->draft()->create(['title' => 'Draft footer', 'slug' => 'draft-footer']);

        SiteSetting::current()->update(['footer_pages' => [$privacy->id, $draft->id, $about->id]]);

        $response = $this->get('/')->assertOk()
            ->assertSee('Privacy footer')
            ->assertSee('About footer')
            ->assertDontSee('Draft footer');

        $this->assertStringContainsString('href="'.url('/privacy-footer').'"', $response->getContent());
        $this->assertStringContainsString('href="'.url('/about-footer').'"', $response->getContent());
        $this->assertLessThan(
            strpos($response->getContent(), 'About footer'),
            strpos($response->getContent(), 'Privacy footer'),
        );
    }

    public function test_admin_can_select_explore_pages_and_builtin_links(): void
    {
        $selected = Page::factory()->create(['title' => 'Explore selected', 'slug' => 'explore-selected']);
        $draft = Page::factory()->draft()->create(['title' => 'Explore draft', 'slug' => 'explore-draft']);

        SiteSetting::current()->update([
            'explore_pages' => [$selected->id, $draft->id],
            'explore_links' => ['contact'],
        ]);

        $this->get('/')->assertOk()
            ->assertSee('Explore selected')
            ->assertDontSee('Explore draft')
            ->assertSee('href="'.url('/contact').'"', false)
            ->assertDontSee('href="'.url('/insights').'"', false)
            ->assertDontSee('href="'.url('/events').'"', false);
    }

    public function test_sitemap_lists_indexable_pages_and_contact(): void
    {
        Page::factory()->create(['slug' => 'about']);
        Page::factory()->create(['slug' => 'private-page', 'noindex' => true]);
        Page::factory()->draft()->create(['slug' => 'draft-page']);

        $this->get('/sitemap.xml')->assertOk()
            ->assertSee(url('/about'))
            ->assertSee(route('contact'))
            ->assertDontSee('private-page')
            ->assertDontSee('draft-page');
    }

    public function test_contact_page_shows_details_grid_map_and_social_with_font_awesome_icons(): void
    {
        SiteSetting::current()->update([
            'contact_email' => 'hello@muhame.test',
            'contact_phone' => '+256 (700) 123-456',
            'contact_address' => "Plot 12, Example Road\nKampala",
            'office_hours' => 'Mon – Fri: 8:00 – 17:00',
            'map_location' => 'Kampala, Uganda',
            'social_links' => ['LinkedIn' => 'https://linkedin.com/company/muhame', 'Facebook' => 'https://facebook.com/muhame'],
        ]);

        $this->get('/contact')->assertOk()
            ->assertSee('fas fa-envelope', false)->assertSee('href="mailto:hello@muhame.test"', false)
            ->assertSee('fas fa-phone', false)->assertSee('href="tel:+256700123456"', false)
            ->assertSee('fas fa-location-dot', false)->assertSee('Plot 12, Example Road')
            ->assertSee('fas fa-clock', false)->assertSee('Mon – Fri: 8:00 – 17:00')
            ->assertSee('src="https://www.google.com/maps?q=Kampala%2C%20Uganda&amp;output=embed"', false)
            ->assertSee('https://www.google.com/maps/dir/?api=1&amp;destination=Kampala%2C%20Uganda', false)
            ->assertSee('fab fa-linkedin-in', false)->assertSee('fab fa-facebook-f', false)
            ->assertSee('fas fa-paper-plane', false);
    }

    public function test_contact_page_hides_empty_details_and_map(): void
    {
        $this->get('/contact')->assertOk()
            ->assertSee('Send us a message')
            ->assertSee('placeholder="you@example.com"', false)
            ->assertSee('placeholder="Tell us how we can help…"', false)
            ->assertDontSee('aria-label="Contact details"', false)
            ->assertDontSee('<iframe', false)
            ->assertDontSee('Get directions');
    }

    public function test_pasted_google_maps_embed_code_is_used_for_the_map(): void
    {
        SiteSetting::current()->update([
            'map_embed_url' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!3d0.34" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>',
        ]);

        $this->get('/contact')->assertOk()
            ->assertSee('src="https://www.google.com/maps/embed?pb=!1m18!1m12!3d0.34"', false)
            ->assertDontSee('Get directions');
    }

    public function test_map_embed_only_accepts_google_maps_or_openstreetmap(): void
    {
        $this->assertSame('https://www.google.com/maps/embed?pb=abc', SiteSetting::extractMapEmbedUrl('https://www.google.com/maps/embed?pb=abc'));
        $this->assertSame('https://www.openstreetmap.org/export/embed.html?bbox=1,2,3,4', SiteSetting::extractMapEmbedUrl("<iframe src='https://www.openstreetmap.org/export/embed.html?bbox=1,2,3,4'></iframe>"));
        $this->assertNull(SiteSetting::extractMapEmbedUrl('<iframe src="https://evil.example.com/maps/embed?pb=x"></iframe>'));
        $this->assertNull(SiteSetting::extractMapEmbedUrl('<iframe src="javascript:alert(1)"></iframe>'));
        $this->assertNull(SiteSetting::extractMapEmbedUrl('http://www.google.com/maps/embed?pb=x'));
        $this->assertNull(SiteSetting::extractMapEmbedUrl('https://www.google.com.evil.com/maps/embed?pb=x'));
    }

    public function test_admin_can_save_embed_code_and_only_the_url_is_stored(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create()->assignRole('Admin'));

        Livewire::test(ManageSiteSettings::class)
            ->fillForm(['map_embed_url' => '<iframe src="https://evil.example.com/x"></iframe>'])
            ->call('save')
            ->assertHasFormErrors(['map_embed_url']);

        Livewire::test(ManageSiteSettings::class)
            ->fillForm(['map_embed_url' => '<iframe src="https://www.google.com/maps/embed?pb=ok" width="600"></iframe>'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('https://www.google.com/maps/embed?pb=ok', SiteSetting::query()->find(1)->map_embed_url);
    }

    public function test_map_location_is_url_encoded_so_it_cannot_break_out_of_the_iframe(): void
    {
        SiteSetting::current()->update(['map_location' => '"><script>alert(1)</script>']);

        $this->get('/contact')->assertOk()->assertDontSee('<script>alert(1)', false)->assertSee('%22%3E%3Cscript%3E', false);
    }

    public function test_contact_form_stores_message_and_emails_site_contact(): void
    {
        Mail::fake();
        SiteSetting::current()->update(['contact_email' => 'inbox@muhame.test']);

        $this->post('/contact', [
            'name' => 'Jane Visitor',
            'email' => 'jane@example.org',
            'subject' => 'Partnership',
            'message' => 'Hello, I would like to work with you.',
        ])->assertRedirect(route('contact'))->assertSessionHas('status');

        $this->assertDatabaseHas('contact_messages', ['email' => 'jane@example.org', 'subject' => 'Partnership', 'read_at' => null]);
        Mail::assertQueued(ContactMessageReceived::class, fn (ContactMessageReceived $mail) => $mail->hasTo('inbox@muhame.test')
            && $mail->hasReplyTo('jane@example.org'));
    }

    public function test_contact_form_validates_input(): void
    {
        Mail::fake();

        $this->post('/contact', ['name' => '', 'email' => 'not-an-email', 'message' => 'short'])
            ->assertSessionHasErrors(['name', 'email', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingQueued();
    }

    public function test_contact_form_silently_drops_honeypot_submissions(): void
    {
        Mail::fake();

        $this->post('/contact', [
            'name' => 'Bot',
            'email' => 'bot@spam.test',
            'message' => 'Buy cheap things now!!!',
            'website' => 'https://spam.test',
        ])->assertRedirect(route('contact'))->assertSessionHas('status');

        $this->assertDatabaseCount('contact_messages', 0);
        Mail::assertNothingQueued();
    }

    public function test_contact_form_is_rate_limited(): void
    {
        Mail::fake();
        $payload = ['name' => 'Jane', 'email' => 'jane@example.org', 'message' => 'A perfectly valid message.'];

        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact', $payload)->assertRedirect();
        }

        $this->post('/contact', $payload)->assertStatus(429);
    }

    public function test_page_and_inbox_admin_access_follows_roles(): void
    {
        $this->seed();
        $admin = User::factory()->create()->assignRole('Admin');
        $editor = User::factory()->create()->assignRole('Editor');
        $author = User::factory()->create()->assignRole('Author');
        $page = Page::factory()->create();

        $this->assertTrue($editor->can('update', $page));
        $this->assertFalse($editor->can('delete', $page));
        $this->assertFalse($author->can('viewAny', Page::class));
        $this->assertTrue($admin->can('viewAny', ContactMessage::class));
        $this->assertFalse($editor->can('viewAny', ContactMessage::class));

        $this->actingAs($admin)->get('/admin/pages')->assertOk();
        $this->actingAs($admin)->get('/admin/contact-messages')->assertOk();
        $this->actingAs($editor)->get('/admin/pages')->assertOk();
        $this->actingAs($editor)->get('/admin/contact-messages')->assertForbidden();
        $this->actingAs($author)->get('/admin/pages')->assertForbidden();
    }

    public function test_admin_can_create_a_page_but_not_with_a_reserved_slug(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create()->assignRole('Admin'));

        Livewire::test(CreatePage::class)
            ->fillForm(['title' => 'Admin', 'slug' => 'admin', 'body' => '<p>Nope</p>', 'status' => 'published'])
            ->call('create')
            ->assertHasFormErrors(['slug']);

        Livewire::test(CreatePage::class)
            ->fillForm(['title' => 'A new page', 'slug' => 'a-new-page', 'body' => '<p>Hello</p>', 'status' => 'published'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseMissing('pages', ['slug' => 'admin']);
        $this->assertDatabaseHas('pages', ['slug' => 'a-new-page', 'status' => 'published']);
        $this->get('/a-new-page')->assertOk()->assertSee('Hello');
    }

    public function test_admin_can_mark_a_message_as_read(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create()->assignRole('Admin'));
        $message = ContactMessage::create(['name' => 'Jane', 'email' => 'jane@example.org', 'message' => 'Hello there, friends.']);

        Livewire::test(ManageContactMessages::class)
            ->callTableAction('markRead', $message)
            ->assertHasNoTableActionErrors();

        $this->assertNotNull($message->fresh()->read_at);
    }
}
