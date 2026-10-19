<?php

namespace Tests\Feature;

use App\Support\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('pagead2.googlesyndication.com', false)
            ->assertDontSee('/guides', false)
            ->assertDontSee('/faq', false)
            ->assertSee('data-update-interval="300"', false)
            ->assertSee('data-share', false)
            ->assertSee('name="msvalidate.01"', false)
            ->assertSee('167843586563944F086753F2A9641BFE', false)
            ->assertSee('data-camera-open', false)
            ->assertSee('data-camera-video', false)
            ->assertSee('data-mode-select', false)
            ->assertSee('value="camera"', false)
            ->assertSee(__('ui.troubleshooting'), false);
    }

    public function test_ad_preview_shows_placeholder_units(): void
    {
        $this->get('/?ads_preview=1')
            ->assertOk()
            ->assertSee('Banner ad preview')
            ->assertDontSee('pagead2.googlesyndication.com', false);
    }

    public function test_removed_content_urls_are_gone(): void
    {
        $this->get('/faq')->assertStatus(410);
        $this->get('/guides')->assertStatus(410);
    }

    public function test_sitemap_and_robots_are_ready_for_search_console(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('http://www.sitemaps.org/schemas/sitemap/0.9', false)
            ->assertSee('/cities', false)
            ->assertSee('/contact', false)
            ->assertDontSee('/faq', false)
            ->assertDontSee('/guides', false)
            ->assertDontSee('/prayer-times/', false);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap:', false)
            ->assertSee('/sitemap.xml', false)
            ->assertSee('Disallow: /admin', false)
            ->assertDontSee('Disallow: /guides', false);

        config(['app.url' => 'http://localhost']);

        $this->get('https://qiblafinders.io/sitemap.xml')
            ->assertOk()
            ->assertSee('<loc>https://qiblafinders.io</loc>', false)
            ->assertDontSee('/prayer-times</loc>', false)
            ->assertDontSee('http://localhost', false);

        $this->get('https://qiblafinders.io/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap: https://qiblafinders.io/sitemap.xml', false);

        $this->assertFileExists(public_path('sitemap.php'));
        $this->assertFileExists(public_path('robots.php'));
    }

    public function test_custom_head_and_footer_html_render(): void
    {
        SiteSettings::put([
            'google_site_verification' => '<meta name="google-site-verification" content="jtIemhVXRpmISnRTllfMePhuh4tKciCLrzFRDSVF6wc" />',
            'head_html' => '<meta name="test-head" content="ok">',
            'footer_html' => '<div id="test-foot"></div>',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('name="google-site-verification"', false)
            ->assertSee('jtIemhVXRpmISnRTllfMePhuh4tKciCLrzFRDSVF6wc', false)
            ->assertSee('<meta name="test-head" content="ok">', false)
            ->assertSee('<div id="test-foot"></div>', false);
    }

    public function test_bing_verification_meta_is_on_the_home_page(): void
    {
        SiteSettings::put([
            'bing_site_verification' => '<meta name="msvalidate.01" content="167843586563944F086753F2A9641BFE" />',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('<meta name="msvalidate.01" content="167843586563944F086753F2A9641BFE">', false);
    }
}
