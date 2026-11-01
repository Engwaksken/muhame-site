<?php

namespace Tests\Feature;

use App\Filament\Resources\ContactMessages\Pages\ManageContactMessages;
use App\Filament\Resources\Pages\Pages\ManagePages;
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

    public function test_navigation_shows_published_menu_pages_in_order(): void
    {
        Page::factory()->inNavigation(2)->create(['title' => 'Services', 'slug' => 'services']);
        Page::factory()->inNavigation(1)->create(['title' => 'About', 'slug' => 'about']);
        Page::factory()->draft()->inNavigation(0)->create(['title' => 'Hidden draft', 'slug' => 'hidden-draft']);
        Page::factory()->create(['title' => 'Not in menu', 'slug' => 'not-in-menu']);

        $this->get('/')->assertOk()
            ->assertSeeInOrder(['href="'.url('/about').'"', 'href="'.url('/services').'"'], false)
            ->assertDontSee('Hidden draft')
            ->assertDontSee('Not in menu')
            ->assertSee('href="'.route('contact').'"', false);
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

        Livewire::test(ManagePages::class)
            ->callAction('create', data: ['title' => 'Admin', 'slug' => 'admin', 'body' => '<p>Nope</p>', 'status' => 'published'])
            ->assertHasActionErrors(['slug']);

        Livewire::test(ManagePages::class)
            ->callAction('create', data: ['title' => 'About us', 'slug' => 'about-us', 'body' => '<p>Hello</p>', 'status' => 'published'])
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('pages', ['slug' => 'admin']);
        $this->assertDatabaseHas('pages', ['slug' => 'about-us', 'status' => 'published']);
        $this->get('/about-us')->assertOk()->assertSee('Hello');
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
