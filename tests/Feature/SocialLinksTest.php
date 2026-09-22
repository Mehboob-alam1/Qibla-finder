<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SocialLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_footer_shows_configured_social_links(): void
    {
        Setting::setValue('facebook', 'https://facebook.com/qiblafinder');
        Setting::setValue('twitter', 'https://x.com/qiblafinder');
        Cache::forget('site_settings');

        $this->get('/')
            ->assertOk()
            ->assertSee('https://facebook.com/qiblafinder', false)
            ->assertSee('https://x.com/qiblafinder', false)
            ->assertSee(__('ui.Follow us'), false);
    }

    public function test_admin_can_save_social_urls(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Qibla Finder',
                'default_calculation_method' => 'MWL',
                'qibla_update_interval' => 300,
                'qibla_display_mode' => 'compass',
                'reddit' => 'https://reddit.com/r/qiblafinder',
                'pinterest' => 'https://pinterest.com/qiblafinder',
            ])
            ->assertRedirect();

        $this->assertSame('https://reddit.com/r/qiblafinder', Setting::query()->where('key', 'reddit')->value('value'));
    }

    public function test_footer_social_hidden_when_setting_off(): void
    {
        Setting::setValue('facebook', 'https://facebook.com/qiblafinder');
        Setting::setValue('social_show_footer', '0');
        Setting::setValue('social_show_header', '1');
        Cache::forget('site_settings');

        $this->get('/')
            ->assertOk()
            ->assertDontSee('social-links--footer', false);
    }
}
