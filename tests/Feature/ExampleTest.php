<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
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
}
