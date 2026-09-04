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
            ->assertDontSee('Banner ad preview')
            ->assertSee('data-update-interval="300"', false)
            ->assertSee('What devices can use this Qibla Finder?', false)
            ->assertSee('chrome://flags/#enable-generic-sensor-extra-classes', false)
            ->assertSee('data-share', false);
    }

    public function test_ad_preview_shows_placeholder_units(): void
    {
        $this->get('/?ads_preview=1')
            ->assertOk()
            ->assertSee('Banner ad preview')
            ->assertDontSee('pagead2.googlesyndication.com', false);
    }

    public function test_faq_page_includes_setup_instructions(): void
    {
        $this->get('/faq')
            ->assertOk()
            ->assertSee('Setup for Chrome', false)
            ->assertSee('Motion &amp; Orientation Access', false);
    }

    public function test_sitemap_and_robots_are_ready_for_search_console(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('http://www.sitemaps.org/schemas/sitemap/0.9', false)
            ->assertSee('/prayer-times', false)
            ->assertSee('/faq', false)
            ->assertSee('/guides', false)
            ->assertSee('/contact', false);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap:', false)
            ->assertSee('/sitemap.xml', false);

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
}
