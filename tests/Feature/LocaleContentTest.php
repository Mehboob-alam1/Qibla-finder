<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_guides_are_gone_while_posts_remain_in_database(): void
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

        $this->assertSame(1, Post::query()->count());
        $this->get('/guides')->assertStatus(410);
        $this->get('/guides/english-ranking-guide')->assertStatus(410);
    }

    public function test_language_switcher_only_on_home(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('/locale/ar', false);

        $this->get('/cities')
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
