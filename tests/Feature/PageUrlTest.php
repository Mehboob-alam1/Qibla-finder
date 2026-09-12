<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_duas_qibla_uses_a_flat_url_like_prayer_times(): void
    {
        $this->page('duas-qibla', 'flat');

        $this->get('/p/duas-qibla')->assertRedirect('/duas-qibla')->assertStatus(301);
        $this->get('/duas-qibla')
            ->assertOk()
            ->assertSee('Duas', false);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/duas-qibla', false)
            ->assertDontSee('/p/duas-qibla', false);
    }

    public function test_prefixed_pages_stay_under_p(): void
    {
        $this->page('about', 'prefixed', 'About');

        $this->get('/about')->assertRedirect('/p/about');
        $this->get('/p/about')->assertOk()->assertSee('About', false);
    }

    public function test_new_pages_must_choose_a_url_structure(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/admin/pages/create')
            ->assertOk()
            ->assertSee('Public URL', false)
            ->assertSee('value="flat"', false)
            ->assertSee('value="prefixed"', false);

        $this->actingAs($admin)
            ->post('/admin/pages', [
                'title' => 'Travel duas',
                'slug' => 'travel-duas',
                'url_style' => 'flat',
                'locale' => 'en',
                'content' => '<p>Peace</p>',
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.pages.index'));

        $this->get('/travel-duas')->assertOk();
        $this->get('/p/travel-duas')->assertRedirect('/travel-duas');
    }

    public function test_admin_chooses_whether_a_page_appears_in_header_or_footer(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/admin/pages/create')
            ->assertOk()
            ->assertSee('Where this link appears', false)
            ->assertSee('Show in header', false)
            ->assertSee('Show in footer', false);

        $this->actingAs($admin)
            ->post('/admin/pages', [
                'title' => 'Duas Qibla',
                'slug' => 'duas-qibla',
                'url_style' => 'flat',
                'locale' => 'en',
                'content' => '<p>Duas</p>',
                'is_published' => '1',
                'show_in_header' => '1',
                'show_in_footer' => '1',
            ])
            ->assertRedirect(route('admin.pages.index'));

        $this->page('about', 'prefixed', 'About');
        Page::query()->where('slug', 'about')->update([
            'show_in_header' => false,
            'show_in_footer' => true,
        ]);

        $home = $this->get('/')->assertOk();
        $home->assertSee('href="/duas-qibla"', false);
        $home->assertSee('>Duas Qibla</a>', false);

        $this->assertSame(3, substr_count($home->getContent(), 'href="/duas-qibla"'));
        $this->assertSame(1, substr_count($home->getContent(), 'href="/p/about"'));
        $home->assertDontSee('nav-link" href="/p/about"', false);
    }

    public function test_pages_hidden_from_nav_do_not_appear_in_header_or_footer(): void
    {
        $this->page('secret-notes', 'flat', 'Secret notes');
        Page::query()->where('slug', 'secret-notes')->update([
            'show_in_header' => false,
            'show_in_footer' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Secret notes', false)
            ->assertDontSee('href="/secret-notes"', false);
    }

    public function test_flat_pages_cannot_take_reserved_app_urls(): void
    {
        $this->actingAs($this->admin())
            ->from('/admin/pages/create')
            ->post('/admin/pages', [
                'title' => 'FAQ copy',
                'slug' => 'faq',
                'url_style' => 'flat',
                'locale' => 'en',
                'content' => '<p>Nope</p>',
                'is_published' => '1',
            ])
            ->assertRedirect('/admin/pages/create')
            ->assertSessionHasErrors('slug');
    }

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    protected function page(string $slug, string $style, string $title = 'Duas'): Page
    {
        return Page::query()->create([
            'title' => $title,
            'slug' => $slug,
            'url_style' => $style,
            'locale' => 'en',
            'content' => '<p>'.$title.'</p>',
            'is_published' => true,
        ]);
    }
}
