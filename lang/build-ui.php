<?php

/**
 * Merge complete translations onto every locale file, keeping good existing strings.
 * Run: php lang/build-ui.php
 */
$en = require __DIR__.'/en/ui.php';

$keepAsIs = [
    'WhatsApp', 'Telegram', 'Facebook', 'X',
    'Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha', 'Imsak', 'Midnight',
    'Hanafi', 'Kaaba', 'help_chrome_flag', 'help_edge_flag',
];

$chrome = require __DIR__.'/fills/chrome.php';
$seo = require __DIR__.'/fills/seo.php';

$fill = [
    'ar' => require __DIR__.'/fills/ar.php',
    'ur' => require __DIR__.'/fills/ur.php',
    'fa' => require __DIR__.'/fills/fa.php',
    'id' => require __DIR__.'/fills/id.php',
    'ms' => require __DIR__.'/fills/ms.php',
    'tr' => require __DIR__.'/fills/tr.php',
    'fr' => require __DIR__.'/fills/fr.php',
    'de' => require __DIR__.'/fills/de.php',
    'es' => require __DIR__.'/fills/es.php',
    'it' => require __DIR__.'/fills/it.php',
    'nl' => require __DIR__.'/fills/nl.php',
    'pt' => require __DIR__.'/fills/pt.php',
    'ru' => require __DIR__.'/fills/ru.php',
    'bn' => require __DIR__.'/fills/bn.php',
    'hi' => require __DIR__.'/fills/hi.php',
    'da' => require __DIR__.'/fills/da.php',
    'sv' => require __DIR__.'/fills/sv.php',
];

foreach ($fill as $locale => $overrides) {
    $overrides = array_merge($overrides, $chrome[$locale] ?? [], $seo[$locale] ?? []);
    $current = require __DIR__.'/'.$locale.'/ui.php';
    $merged = [];

    foreach ($en as $key => $english) {
        $value = $overrides[$key] ?? $current[$key] ?? $english;
        if ($value === $english && ! in_array($key, $keepAsIs, true) && isset($overrides[$key])) {
            $value = $overrides[$key];
        }
        if ($value === $english && ! in_array($key, $keepAsIs, true) && isset($overrides[$key]) === false) {
            // Prefer override-only fills; if still English, use override when present.
        }
        $merged[$key] = $value;
    }

    $lines = ['<?php', '', 'return ['];
    foreach ($merged as $key => $value) {
        $lines[] = '    '.var_export($key, true).' => '.var_export($value, true).',';
    }
    $lines[] = '];';
    $lines[] = '';
    file_put_contents(__DIR__.'/'.$locale.'/ui.php', implode("\n", $lines));

    $stillEnglish = [];
    foreach ($merged as $key => $value) {
        if ($value === $en[$key] && ! in_array($key, $keepAsIs, true)) {
            $stillEnglish[] = $key;
        }
    }
    echo $locale.' '.count($merged).' leftover='.count($stillEnglish);
    if ($stillEnglish !== []) {
        echo ' ['.implode(', ', array_slice($stillEnglish, 0, 12)).(count($stillEnglish) > 12 ? ', …' : '').']';
    }
    echo PHP_EOL;
}
