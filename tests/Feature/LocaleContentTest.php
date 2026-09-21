<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guides_list_shows_only_current_locale_articles(): void
    {
        Post::query()->create([
            'title' => 'English ranking guide',
            'slug' => 'english-ranking-guide',
            'locale' => 'en',
            'excerpt' => 'English excerpt',
            'content' => '<p>English body</p>',
            'is_published' => true,
            'published_at' => now(),
        ]);

        Post::query()->create([
            'title' => 'دليل بالعربية',
            'slug' => 'arabic-guide',
            'locale' => 'ar',
            'excerpt' => 'ملخص',
            'content' => '<p>محتوى</p>',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->get('/guides?hl=ar')
            ->assertOk()
            ->assertSee('دليل بالعربية', false)
            ->assertDontSee('English ranking guide', false);

        $this->get('/guides?hl=en')
            ->assertOk()
            ->assertSee('English ranking guide', false)
            ->assertDontSee('دليل بالعربية', false);
    }

    public function test_english_guide_is_not_accessible_under_arabic_locale(): void
    {
        Post::query()->create([
            'title' => 'English only',
            'slug' => 'english-only',
            'locale' => 'en',
            'excerpt' => 'x',
            'content' => '<p>x</p>',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->get('/guides/english-only?hl=ar')->assertNotFound();
        $this->get('/guides/english-only?hl=en')->assertOk()->assertSee('English only', false);
    }

    public function test_same_slug_can_exist_per_locale(): void
    {
        Post::query()->create([
            'title' => 'Calibrate EN',
            'slug' => 'calibrate-compass',
            'locale' => 'en',
            'excerpt' => 'en',
            'content' => '<p>en</p>',
            'is_published' => true,
            'published_at' => now(),
        ]);

        Post::query()->create([
            'title' => 'Calibrate UR',
            'slug' => 'calibrate-compass',
            'locale' => 'ur',
            'excerpt' => 'ur',
            'content' => '<p>ur</p>',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->assertSame(2, Post::query()->where('slug', 'calibrate-compass')->count());

        $this->get('/guides?hl=ur')
            ->assertOk()
            ->assertSee('Calibrate UR', false)
            ->assertDontSee('Calibrate EN', false);
    }

    public function test_language_switcher_only_on_home(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('/locale/ar', false);

        $this->get('/guides')
            ->assertOk()
            ->assertDontSee('/locale/ar', false);

        Post::query()->create([
            'title' => 'Sample guide',
            'slug' => 'sample-guide',
            'locale' => 'en',
            'excerpt' => 'x',
            'content' => '<p>x</p>',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->get('/guides/sample-guide')
            ->assertOk()
            ->assertDontSee('/locale/ar', false);
    }

    public function test_changing_locale_from_guides_redirects_to_home(): void
    {
        $this->from('/guides')
            ->get('/locale/ur')
            ->assertRedirect('/?hl=ur');
    }
}
