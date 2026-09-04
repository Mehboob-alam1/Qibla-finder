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
            ->assertDontSee('Banner ad preview');
    }

    public function test_ad_preview_shows_placeholder_units(): void
    {
        $this->get('/?ads_preview=1')
            ->assertOk()
            ->assertSee('Banner ad preview')
            ->assertDontSee('pagead2.googlesyndication.com', false);
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
