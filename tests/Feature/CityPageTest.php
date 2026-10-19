<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_city_qibla_page_is_indexable_with_prayer_times(): void
    {
        $this->get('/qibla/london')
            ->assertOk()
            ->assertSee('Qibla direction in London', false)
            ->assertSee('United Kingdom', false)
            ->assertSee('Fajr', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false)
            ->assertSee('hreflang="ar"', false)
            ->assertSee('rel="canonical"', false);

        $this->get('/cities')
            ->assertOk()
            ->assertSee('London', false)
            ->assertSee('/qibla/new-york', false)
            ->assertSee('data-city-filter', false)
            ->assertDontSee('/prayer-times/', false);
    }

    public function test_sitemap_lists_city_pages_not_prayer_city_pages(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/cities', false)
            ->assertSee('/qibla/london', false)
            ->assertDontSee('/prayer-times/london', false)
            ->assertSee('/qibla/london?hl=ar', false);
    }

    public function test_unknown_city_is_not_found(): void
    {
        $this->get('/qibla/atlantis')->assertNotFound();
    }

    public function test_hl_query_switches_locale_for_hreflang_urls(): void
    {
        $this->get('/qibla/istanbul?hl=ar')
            ->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('اتجاه القبلة في Istanbul', false);
    }
}
