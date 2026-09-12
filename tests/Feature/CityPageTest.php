<?php

namespace Tests\Feature;

use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_city_qibla_and_prayer_pages_are_indexable(): void
    {
        $this->get('/qibla/london')
            ->assertOk()
            ->assertSee('Qibla direction in London', false)
            ->assertSee('United Kingdom', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false)
            ->assertSee('hreflang="ar"', false)
            ->assertSee('rel="canonical"', false);

        $this->get('/prayer-times/dubai')
            ->assertOk()
            ->assertSee('Prayer times in Dubai', false)
            ->assertSee('Fajr', false)
            ->assertSee('Monthly timetable', false);

        $this->get('/cities')
            ->assertOk()
            ->assertSee('London', false)
            ->assertSee('/qibla/new-york', false);
    }

    public function test_sitemap_lists_city_pages(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/cities', false)
            ->assertSee('/qibla/london', false)
            ->assertSee('/prayer-times/london', false);
    }

    public function test_unknown_city_is_not_found(): void
    {
        $this->get('/qibla/atlantis')->assertNotFound();
    }

    public function test_faq_includes_structured_data(): void
    {
        Faq::query()->create([
            'question' => 'Why does the compass need my location?',
            'answer' => '<p>The Qibla is different in every city.</p>',
            'category' => 'qibla',
            'locale' => 'en',
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $this->get('/faq')
            ->assertOk()
            ->assertSee('application/ld+json', false)
            ->assertSee('FAQPage', false);
    }

    public function test_hl_query_switches_locale_for_hreflang_urls(): void
    {
        $this->get('/qibla/istanbul?hl=ar')
            ->assertOk()
            ->assertSee('lang="ar"', false);
    }
}
