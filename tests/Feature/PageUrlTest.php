<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_legal_cms_pages_are_gone(): void
    {
        $this->page('duas-qibla', 'flat');

        $this->get('/duas-qibla')->assertStatus(410);
        $this->get('/p/duas-qibla')->assertStatus(410);
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/duas-qibla', false);
    }

    public function test_prefixed_legal_pages_stay_under_p(): void
    {
        $this->page('about', 'prefixed', 'About');

        $this->get('/about')->assertRedirect('/p/about');
        $this->get('/p/about')->assertOk()->assertSee('About', false);
    }

    public function test_admin_can_still_create_pages_but_public_only_serves_legal(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/pages', [
                'title' => 'Travel notes',
                'slug' => 'travel-notes',
                'url_style' => 'flat',
                'locale' => 'en',
                'content' => '<p>Peace</p>',
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.pages.index'));

        $this->get('/travel-notes')->assertStatus(410);
        $this->get('/p/travel-notes')->assertStatus(410);
    }

    public function test_legal_pages_appear_in_footer_when_flagged(): void
    {
        $this->page('about', 'prefixed', 'About');
        Page::query()->where('slug', 'about')->update([
            'show_in_header' => false,
            'show_in_footer' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('href="/p/about"', false)
            ->assertDontSee('href="/duas-qibla"', false);
    }

    public function test_pages_hidden_from_nav_do_not_appear_in_header_or_footer(): void
    {
        $this->page('about', 'prefixed', 'About');
        Page::query()->where('slug', 'about')->update([
            'show_in_header' => false,
            'show_in_footer' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('href="/p/about"', false);
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

    protected function page(string $slug, string $style, string $title = 'Page'): Page
    {
        return Page::query()->create([
            'title' => $title,
            'slug' => $slug,
            'url_style' => $style,
            'locale' => 'en',
            'content' => '<p>'.$title.'</p>',
            'is_published' => true,
            'show_in_footer' => true,
        ]);
    }
}
