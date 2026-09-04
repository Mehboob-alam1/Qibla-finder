<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('faqs')) {
            return;
        }

        $now = now();
        $faqs = [
            [
                'question' => 'How to use this Qibla Finder?',
                'answer' => 'Open this page, tap Find Qibla Direction, and allow location when asked. Hold the phone flat — the gold Kaaba marker points to the side where the Qibla is. Turn until it meets the notch at the top. If it does not move, see the Chrome, Edge, or Safari setup on this page.',
                'category' => 'qibla',
                'sort_order' => 1,
            ],
            [
                'question' => 'What devices can use this Qibla Finder?',
                'answer' => 'Any device with a magnetometer will work, Android or iOS. Even older and inexpensive phones usually have this sensor. Laptops typically do not — use the numeric bearing and the map, or open the site on a phone.',
                'category' => 'qibla',
                'sort_order' => 2,
            ],
            [
                'question' => 'Qibla Finder does not work. What should I do?',
                'answer' => 'Allow location and motion/orientation sensors in your browser. Chrome and Edge may need Generic Sensor Extra Classes enabled (chrome://flags/#enable-generic-sensor-extra-classes or the matching edge://flags page). Safari needs Motion & Orientation Access.',
                'category' => 'qibla',
                'sort_order' => 3,
            ],
            [
                'question' => 'Is the Qibla Finder accurate?',
                'answer' => 'The bearing to the Kaaba is a precise geodesic. Remaining error is almost always the device compass. Move the phone in a figure-8 a few times, keep it away from metal, and hold it flat.',
                'category' => 'qibla',
                'sort_order' => 4,
            ],
        ];

        foreach ($faqs as $faq) {
            $existing = DB::table('faqs')->where('question', $faq['question'])->where('locale', 'en')->first();
            $payload = [
                ...$faq,
                'locale' => 'en',
                'is_published' => true,
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('faqs')->where('id', $existing->id)->update($payload);

                continue;
            }

            DB::table('faqs')->insert([
                ...$payload,
                'created_at' => $now,
            ]);
        }
    }
};
