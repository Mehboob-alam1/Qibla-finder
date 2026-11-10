<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PageUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_prefixed_pages_stay_under_p(): void
    {
        $this->page('about', 'prefixed', 'About');

        $this->get('/about')->assertRedirect('/p/about');
        $this->get('/p/about')->assertOk()->assertSee('About', false);
    }

    public function test_flat_cms_pages_are_public_when_published(): void
    {
        $this->page('travel-notes', 'flat', 'Travel notes');

        $this->get('/travel-notes')
            ->assertOk()
            ->assertSee('Travel notes', false);
    }

    public function test_admin_can_create_a_page(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/pages', [
                'title' => 'Help Center',
                'slug' => 'help-center',
                'url_style' => 'prefixed',
                'locale' => 'en',
                'content' => '<p>How to use the compass.</p>',
                'is_published' => '1',
                'show_in_footer' => '1',
                'show_page_title' => '1',
            ])
            ->assertRedirect(route('admin.pages.index'));

        $this->get('/p/help-center')
            ->assertOk()
            ->assertSee('How to use the compass.', false);
    }

    public function test_admin_can_import_html_into_page_fields(): void
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html><head><title>Imported Guide</title><meta name="description" content="From file"></head>
<body><section><h1>Imported Guide</h1><p>Perfect body copy.</p></section></body></html>
HTML;

        $response = $this->actingAs($this->admin())
            ->postJson(route('admin.pages.import-html'), [
                'html' => $html,
            ])
            ->assertOk()
            ->assertJsonPath('title', 'Imported Guide')
            ->assertJsonPath('slug', 'imported-guide')
            ->assertJsonPath('meta_description', 'From file');

        $content = $response->json('content');
        $this->assertStringContainsString('Imported Guide', $content);
        $this->assertStringContainsString('Perfect body copy.', $content);
        $this->assertStringNotContainsString('<script', $content);
    }

    public function test_admin_can_import_uploaded_html_file(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'guide.html',
            '<html><head><title>File Page</title></head><body><p>From upload</p></body></html>',
        );

        $this->actingAs($this->admin())
            ->post(route('admin.pages.import-html'), ['file' => $file], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('title', 'File Page')
            ->assertJsonPath('slug', 'file-page');
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
            'show_page_title' => true,
        ]);
    }
}
