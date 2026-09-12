<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\HtmlContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_editor_is_available_in_admin(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/posts/create')
            ->assertOk()
            ->assertSee('data-editor', false)
            ->assertSee('tinymce', false);
    }

    public function test_article_html_is_sanitized_and_headings_are_kept(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/posts', [
                'title' => 'How Qibla works',
                'locale' => 'en',
                'excerpt' => 'A short guide',
                'content' => '<h2>Heading</h2><p>Body</p><script>alert(1)</script><img src="/media/articles/demo.jpg" alt="Kaaba" class="content-img-md">',
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.posts.index'));

        $this->assertDatabaseHas('posts', ['title' => 'How Qibla works']);
        $this->assertStringContainsString('<h2>Heading</h2>', HtmlContent::clean('<h2>Heading</h2><script>alert(1)</script>'));
        $this->assertStringNotContainsString('<script>', HtmlContent::clean('<p>Safe</p><script>alert(1)</script>'));
    }

    public function test_admin_can_upload_an_article_image(): void
    {
        $response = $this->actingAs($this->admin())
            ->post('/admin/media', [
                'file' => UploadedFile::fake()->image('kaaba.jpg', 640, 480),
            ]);

        $response->assertOk();
        $location = $response->json('location');
        $this->assertIsString($location);
        $this->assertStringStartsWith('/media/articles/', $location);
        $this->assertFileExists(public_path(ltrim($location, '/')));
        File::delete(public_path(ltrim($location, '/')));
    }

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }
}
