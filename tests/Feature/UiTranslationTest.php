<?php

namespace Tests\Feature;

use Tests\TestCase;

class UiTranslationTest extends TestCase
{
    public function test_every_locale_covers_the_english_ui_keys(): void
    {
        $english = require lang_path('en/ui.php');

        foreach (array_keys(config('qibla.locales')) as $locale) {
            $translations = require lang_path($locale.'/ui.php');

            $this->assertSame(
                array_keys($english),
                array_keys($translations),
                "Locale [{$locale}] must define the same UI keys as English, in the same order.",
            );
        }
    }

    public function test_arabic_renders_translated_chrome_not_english_leftovers(): void
    {
        $this->get('/?hl=ar')
            ->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('اتجاه القبلة', false)
            ->assertSee('مواقيت الصلاة', false)
            ->assertSee('قبلة فايندر — اتجاه قبلة دقيق ومواقيت صلاة', false)
            ->assertDontSee('Qibla direction and prayer times by city', false);
    }

    public function test_new_product_copy_is_translated_in_every_locale(): void
    {
        $english = require lang_path('en/ui.php');
        $mustDiffer = [
            'Cities',
            'cities_index_title',
            'cities_index_lead',
            'city_qibla_title',
            'city_prayer_title',
            'install_app',
            'offline_title',
            'camera_unavailable',
            'help_title',
            'Share this site',
            'Display Mode',
            'footer_text',
            'meta_title',
            'prayer_times_desc',
            'seo_qibla_h2_a',
        ];

        foreach (array_keys(config('qibla.locales')) as $locale) {
            if ($locale === 'en') {
                continue;
            }

            $translations = require lang_path($locale.'/ui.php');

            foreach ($mustDiffer as $key) {
                $this->assertNotSame(
                    $english[$key],
                    $translations[$key],
                    "Locale [{$locale}] still uses English for [{$key}].",
                );
            }
        }
    }

    public function test_language_switcher_replaces_hl_on_the_previous_page(): void
    {
        $this->from('/cities?hl=ar')
            ->get('/locale/fr')
            ->assertRedirect()
            ->assertRedirectContains('hl=fr')
            ->assertRedirectContains('/cities');
    }

    public function test_arabic_city_pages_use_translated_templates(): void
    {
        $this->get('/cities?hl=ar')
            ->assertOk()
            ->assertSee('اتجاه القبلة ومواقيت الصلاة حسب المدينة', false)
            ->assertDontSee('Qibla direction and prayer times by city', false);

        $this->get('/qibla/istanbul?hl=ar')
            ->assertOk()
            ->assertSee('اتجاه القبلة في Istanbul', false);
    }
}
