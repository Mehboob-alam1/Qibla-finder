<?php

namespace Tests\Feature;

use App\Support\Cities;
use Tests\TestCase;

class SeoLandingTest extends TestCase
{
    public function test_indonesian_qibla_page_uses_search_phrases_and_hreflang(): void
    {
        $this->get('/kiblat-online')
            ->assertOk()
            ->assertSee('lang="id"', false)
            ->assertSee('Kiblat Online', false)
            ->assertSee('hreflang="ms"', false)
            ->assertSee('/kiblat"', false)
            ->assertSee('hreflang="en"', false)
            ->assertSee('Jakarta', false);
    }

    public function test_malay_qibla_page_uses_search_phrases(): void
    {
        $this->get('/kiblat')
            ->assertOk()
            ->assertSee('lang="ms"', false)
            ->assertSee('Kuala Lumpur', false)
            ->assertSee('/kiblat-online', false);
    }

    public function test_english_home_targets_find_the_qibla(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Find the Qibla', false)
            ->assertSee('hreflang="id"', false)
            ->assertSee('/kiblat-online', false)
            ->assertSee('hreflang="ms"', false);
    }

    public function test_keyword_aliases_redirect_to_the_primary_localized_url(): void
    {
        $this->get('/find-the-qibla')->assertRedirect('/')->assertStatus(301);
        $this->get('/cek-kiblat')->assertRedirect('/kiblat-online')->assertStatus(301);
        $this->get('/kiblat-arah-mana')->assertRedirect('/kiblat-online')->assertStatus(301);
        $this->get('/cari-kiblat')->assertRedirect('/kiblat')->assertStatus(301);
        $this->get('/kompas-kiblat')->assertRedirect('/kiblat')->assertStatus(301);
    }

    public function test_hl_query_on_english_home_moves_to_dedicated_slugs(): void
    {
        $this->get('/?hl=id')->assertRedirect('/kiblat-online')->assertStatus(301);
        $this->get('/?hl=ms')->assertRedirect('/kiblat')->assertStatus(301);
    }

    public function test_legacy_prayer_landings_redirect_into_budget(): void
    {
        $this->get('/prayer-times')->assertRedirect('/');
        $this->get('/jadwal-sholat')->assertRedirect('/kiblat-online');
        $this->get('/waktu-solat')->assertRedirect('/kiblat');
    }

    public function test_language_switcher_moves_between_dedicated_qibla_urls(): void
    {
        $this->from('/kiblat-online')
            ->get('/locale/ms')
            ->assertRedirect('/kiblat');

        $this->from('/kiblat')
            ->get('/locale/en')
            ->assertRedirect('/');
    }

    public function test_sitemap_lists_localized_core_pages_within_budget(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString('/kiblat-online', $xml);
        $this->assertStringContainsString('/kiblat<', $xml);
        $this->assertStringContainsString('/cities', $xml);
        $this->assertStringContainsString('/contact', $xml);
        $this->assertStringContainsString('/qibla/jakarta', $xml);
        $this->assertStringContainsString('hreflang="id"', $xml);
        $this->assertStringContainsString('xmlns:xhtml', $xml);

        $this->assertStringNotContainsString('/jadwal-sholat', $xml);
        $this->assertStringNotContainsString('/guides', $xml);
        $this->assertStringNotContainsString('/faq', $xml);
        $this->assertStringNotContainsString('/prayer-times/', $xml);

        $localeCount = count(config('qibla.locales'));
        $cityCount = Cities::indexable()->count();
        // home + cities hub + contact + city pages, times locales (plus legal CMS rows when seeded)
        $expectedMax = ($cityCount + 4) * $localeCount + 50;

        $this->assertLessThanOrEqual($expectedMax, substr_count($xml, '<loc>'));
        $this->assertGreaterThan($cityCount, substr_count($xml, '<loc>'));
    }
}
