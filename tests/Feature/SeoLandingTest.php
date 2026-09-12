<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoLandingTest extends TestCase
{
    public function test_indonesian_qibla_page_uses_search_phrases_and_hreflang(): void
    {
        $this->get('/kiblat-online')
            ->assertOk()
            ->assertSee('lang="id"', false)
            ->assertSee('Kiblat Online', false)
            ->assertSee('Cek Kiblat', false)
            ->assertSee('kiblat arah mana', false)
            ->assertSee('kompas arah kiblat', false)
            ->assertSee('arah kiblat sholat', false)
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
            ->assertSee('Cari Kiblat', false)
            ->assertSee('kompas kiblat', false)
            ->assertSee('Qibla finder online', false)
            ->assertSee('Kuala Lumpur', false)
            ->assertSee('/kiblat-online', false);
    }

    public function test_english_home_targets_find_the_qibla(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Find the Qibla', false)
            ->assertSee('Qibla finder online', false)
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

    public function test_hl_query_on_english_core_pages_moves_to_dedicated_slugs(): void
    {
        $this->get('/?hl=id')->assertRedirect('/kiblat-online')->assertStatus(301);
        $this->get('/?hl=ms')->assertRedirect('/kiblat')->assertStatus(301);
        $this->get('/prayer-times?hl=id')->assertRedirect('/jadwal-sholat')->assertStatus(301);
        $this->get('/prayer-times?hl=ms')->assertRedirect('/waktu-solat')->assertStatus(301);
    }

    public function test_indonesian_and_malay_prayer_pages_use_local_titles(): void
    {
        $this->get('/jadwal-sholat')
            ->assertOk()
            ->assertSee('lang="id"', false)
            ->assertSee('Jadwal Sholat', false);

        $this->get('/waktu-solat')
            ->assertOk()
            ->assertSee('lang="ms"', false)
            ->assertSee('Waktu Solat', false);
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

    public function test_sitemap_lists_localized_core_pages(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/kiblat-online', false)
            ->assertSee('/kiblat<', false)
            ->assertSee('/jadwal-sholat', false)
            ->assertSee('/waktu-solat', false)
            ->assertSee('hreflang="id"', false)
            ->assertSee('xmlns:xhtml', false);
    }
}
