<?php

namespace Tests\Feature;

use App\Support\Cities;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgrammaticSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_guides_and_faq_return_gone(): void
    {
        $this->get('/guides')->assertStatus(410);
        $this->get('/guides/how-qibla-is-calculated')->assertStatus(410);
        $this->get('/faq')->assertStatus(410);
        $this->get('/duas-qibla')->assertStatus(410);
        $this->get('/blog')->assertStatus(410);
        $this->get('/rss')->assertStatus(410);
        $this->get('/search')->assertStatus(410);
    }

    public function test_prayer_paths_redirect_into_budget(): void
    {
        $this->get('/prayer-times')->assertRedirect('/');
        $this->get('/prayer-times/london')->assertRedirect('/qibla/london');
        $this->get('/jadwal-sholat')->assertRedirect('/kiblat-online');
    }

    public function test_sitemap_only_contains_budget_urls(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringNotContainsString('/guides', $xml);
        $this->assertStringNotContainsString('/faq', $xml);
        $this->assertStringNotContainsString('/prayer-times/', $xml);
        $this->assertStringContainsString('/cities', $xml);
        $this->assertStringContainsString('/qibla/london', $xml);
        $this->assertStringContainsString('/contact', $xml);
    }

    public function test_sitemap_urls_are_self_consistent(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        preg_match_all('#<loc>([^<]+)</loc>#', $xml, $matches);
        $locs = $matches[1] ?? [];

        $this->assertNotEmpty($locs);
        $this->assertLessThanOrEqual(2500, count($locs), 'Sitemap exceeds URL budget');

        foreach (array_slice($locs, 0, 40) as $loc) {
            $path = parse_url($loc, PHP_URL_PATH) ?: '/';
            $query = parse_url($loc, PHP_URL_QUERY);
            $url = $path.($query ? '?'.$query : '');

            $response = $this->get($url);
            $this->assertTrue(
                $response->isOk(),
                "Sitemap URL not 200: {$url} → ".$response->status(),
            );
            $this->assertStringNotContainsString(
                'noindex',
                strtolower($response->headers->get('X-Robots-Tag', '').' '.$response->getContent()),
                "Sitemap URL is noindex: {$url}",
            );
        }
    }

    public function test_non_indexable_city_redirects_to_nearest(): void
    {
        config([
            'qibla.cities' => [
                ['name' => 'Makkah', 'country' => 'Saudi Arabia', 'lat' => 21.4225, 'lng' => 39.8262, 'is_indexable' => true],
                ['name' => 'Tinyville', 'country' => 'Testland', 'lat' => 21.5, 'lng' => 39.9, 'is_indexable' => false],
            ],
        ]);

        $this->get('/qibla/tinyville')->assertRedirect('/qibla/makkah');
        $this->assertTrue(Cities::find('tinyville')['is_indexable'] === false);
    }

    public function test_robots_disallows_admin_and_ajax_not_guides(): void
    {
        $body = $this->get('/robots.txt')->assertOk()->getContent();
        $this->assertStringContainsString('Disallow: /admin', $body);
        $this->assertStringContainsString('Disallow: /places/search', $body);
        $this->assertStringNotContainsString('Disallow: /guides', $body);
    }

    public function test_homepage_has_no_guides_or_faq_sections(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('route(\'blog.index\')', false)
            ->assertDontSee('/guides', false)
            ->assertSee(__('ui.troubleshooting'), false);
    }
}
