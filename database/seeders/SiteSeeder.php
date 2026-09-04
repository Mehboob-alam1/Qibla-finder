<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => 'Qibla Finder',
            'tagline' => 'Face the Kaaba with certainty — anywhere on Earth.',
            'contact_email' => 'hello@qiblafinder.test',
            'contact_phone' => '',
            'address' => '',
            'footer_text' => 'Built for Muslims who want a precise, beautiful Qibla and prayer companion.',
            'meta_title' => 'Qibla Finder — Accurate Qibla Direction & Prayer Times',
            'meta_description' => 'Find the Qibla with a live compass, see your distance to the Kaaba, and get precise prayer times for your location. Works on iPhone, Android, and desktop.',
            'facebook' => '',
            'instagram' => '',
            'twitter' => '',
            'youtube' => '',
            'ga_id' => '',
            'adsense_enabled' => '0',
            'adsense_client' => '',
            'adsense_banner_slot' => '',
            'adsense_native_slot' => '',
            'default_calculation_method' => 'MWL',
            'announcement' => '',
            'google_site_verification' => '',
            'head_html' => '',
            'footer_html' => '',
        ];

        foreach ($settings as $key => $value) {
            Setting::setValue($key, $value);
        }

        $this->pages();
        $this->posts();
        $this->faqs();
    }

    protected function pages(): void
    {
        $pages = [
            [
                'title' => 'About',
                'slug' => 'about',
                'meta_title' => 'About Qibla Finder',
                'meta_description' => 'Why we built a more accurate, more beautiful Qibla compass and prayer times companion.',
                'content' => <<<'HTML'
<p>Qibla Finder is a modern companion for salah. It combines a live device compass, true-north bearing to the Kaaba, and location-aware prayer times in one calm, fast experience.</p>
<p>The Qibla is calculated with the great-circle geodesic to the Kaaba in Masjid al-Haram (21.422487° N, 39.826206° E). Prayer times use established astronomical methods such as the Muslim World League, Umm al-Qura, ISNA, Egypt, and Karachi — all configurable from the prayer times screen and the admin panel.</p>
<p>We designed this product to feel premium on a phone in a quiet room before Fajr, and equally usable on a laptop when you are travelling. Location never leaves your device unless you ask us to calculate times through the site.</p>
<h2>What makes it different</h2>
<ul>
<li>A live magnetometer compass with alignment feedback, not a static arrow.</li>
<li>Manual city search when GPS is blocked, plus a world map to the Kaaba.</li>
<li>Hijri date, next-prayer countdown, and monthly timetables.</li>
<li>English and Arabic (RTL), with more locales ready in the admin.</li>
<li>A full content and settings admin so you can run this as a real product.</li>
</ul>
HTML,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy',
                'meta_title' => 'Privacy Policy',
                'meta_description' => 'How Qibla Finder handles location, cookies, and contact messages.',
                'content' => <<<'HTML'
<p>Your salah is private. This policy explains what this website does and does not collect.</p>
<h2>Location</h2>
<p>Qibla direction and prayer times are computed from coordinates you share with the browser. Coordinates used for the on-page compass stay in your browser. If you request a server-side prayer timetable, we process latitude, longitude, timezone, and method for that request only — we do not create a location profile.</p>
<h2>Analytics</h2>
<p>Anonymous page-path counts may be stored to understand which tools are useful. We do not sell personal data.</p>
<h2>Contact form</h2>
<p>If you write to us, we store your name, email, and message so we can reply. You may ask us to delete that record.</p>
<h2>Cookies</h2>
<p>A language cookie remembers your locale. An admin session cookie is used only after you sign in to the dashboard.</p>
<h2>Advertising</h2>
<p>If ads are enabled, Google AdSense may show a small number of banner, native, and occasional full-screen ads. Google may use cookies or similar technology to serve those ads. You can learn more in Google’s advertising policies. The Qibla compass itself is never covered by an ad on first load.</p>
HTML,
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms',
                'meta_title' => 'Terms of Service',
                'meta_description' => 'Terms for using Qibla Finder.',
                'content' => <<<'HTML'
<p>Qibla Finder is provided as a free worship aid. Compass accuracy depends on your device magnetometer, calibration, metal interference, and browser permissions. Prayer times depend on the calculation method you choose and your coordinates.</p>
<p>This tool does not issue religious rulings. When in doubt, follow your local mosque or scholar. We are not liable for missed prayers or heading error caused by hardware, OS restrictions, or incorrect settings.</p>
<p>You may use the site for personal, non-commercial worship. Do not abuse the contact form or attempt to disrupt the service.</p>
HTML,
            ],
        ];

        foreach ($pages as $i => $page) {
            Page::query()->updateOrCreate(
                ['slug' => $page['slug'], 'locale' => 'en'],
                $page + ['locale' => 'en', 'is_published' => true, 'sort_order' => $i + 1],
            );
        }
    }

    protected function posts(): void
    {
        $posts = [
            [
                'title' => 'How the Qibla is calculated (and why your phone sometimes disagrees)',
                'slug' => 'how-qibla-is-calculated',
                'excerpt' => 'The bearing to the Kaaba is a geodesic on a sphere. The hard part is the compass in your hand.',
                'content' => <<<'HTML'
<p>From any point on Earth, the Qibla is the initial bearing of the shortest path to the Kaaba. That path is a great circle. The formula uses your latitude and longitude and the Kaaba’s coordinates inside Masjid al-Haram.</p>
<p>Two phones in the same room can still show different headings. Magnetometers drift near speakers, cases with magnets, car dashboards, and reinforced concrete. iOS Safari also requires Motion &amp; Orientation Access. Recalibrate by moving the phone in a figure-8, then hold it flat.</p>
<p>When the live compass is unavailable (desktop, denied sensors), use the map and the numeric bearing. Place a physical compass beside the screen, or align the map’s north with the street outside.</p>
HTML,
            ],
            [
                'title' => 'Choosing a prayer calculation method',
                'slug' => 'prayer-calculation-methods',
                'excerpt' => 'MWL, Umm al-Qura, ISNA, Egypt, Karachi — what actually changes, and what to pick when you travel.',
                'content' => <<<'HTML'
<p>Fajr and Isha are defined by the sun’s depression angle below the horizon. Different institutions chose different angles, which is why times shift by several minutes between methods.</p>
<ul>
<li><strong>Muslim World League</strong> — a solid default worldwide (Fajr 18°, Isha 17°).</li>
<li><strong>Umm al-Qura (Makkah)</strong> — used in Saudi Arabia; Isha is a fixed interval after Maghrib.</li>
<li><strong>ISNA</strong> — common in North America (15° / 15°).</li>
<li><strong>Egypt</strong> — deeper Fajr angle, widely used in Africa.</li>
<li><strong>Karachi</strong> — common in South Asia.</li>
</ul>
<p>Asr has two schools: the standard (Shafi‘i, Maliki, Hanbali) shadow factor of 1, and the Hanafi factor of 2. Match the method your local mosque prints on its timetable.</p>
HTML,
            ],
            [
                'title' => 'Calibrate your compass on iPhone and Android',
                'slug' => 'calibrate-compass',
                'excerpt' => 'A two-minute setup that fixes most “the arrow is wrong” reports.',
                'content' => <<<'HTML'
<p><strong>iPhone (Safari):</strong> Settings → Privacy &amp; Security → Location Services → on. Then Settings → Safari → Motion &amp; Orientation Access → on. Allow location for this site when prompted.</p>
<p><strong>Android (Chrome):</strong> Allow location. If the heading never moves, open <code>chrome://flags/#enable-generic-sensor-extra-classes</code> and enable Generic Sensor Extra Classes, then restart Chrome.</p>
<p>Hold the phone flat, sweep a figure-8 several times, and stay away from metal tables. The gold ring on our compass turns green when you are within a few degrees of the Kaaba.</p>
HTML,
            ],
        ];

        foreach ($posts as $post) {
            Post::query()->updateOrCreate(
                ['slug' => $post['slug']],
                $post + [
                    'locale' => 'en',
                    'is_published' => true,
                    'published_at' => now()->subDays(rand(1, 12)),
                    'meta_title' => $post['title'],
                    'meta_description' => $post['excerpt'],
                ],
            );
        }
    }

    protected function faqs(): void
    {
        $faqs = [
            ['Why does the compass need my location?', 'The Qibla is different in every city. We use your coordinates only to compute the bearing and distance to the Kaaba. You can also pick a city manually.', 'qibla', 1],
            ['The arrow does not move on my laptop.', 'Most laptops have no magnetometer. Use the map, the numeric bearing, or open the site on your phone for a live compass.', 'qibla', 2],
            ['Is this accurate enough for salah?', 'The geodesic is astronomically precise. Remaining error is almost always the device compass or an uncalibrated sensor. Calibrate, then confirm with a physical compass if you can.', 'qibla', 3],
            ['Which prayer method should I use?', 'Use the same method as your local mosque. If you are unsure, Muslim World League is a respected worldwide default.', 'prayer', 4],
            ['Do you work offline?', 'After the page loads, the compass and last-known location continue to work if the tab stays open. A full offline install is available as a Progressive Web App on supported browsers.', 'general', 5],
            ['Can I use Arabic?', 'Yes. Switch language from the globe menu. Arabic uses a full right-to-left layout.', 'general', 6],
        ];

        foreach ($faqs as [$question, $answer, $category, $order]) {
            Faq::query()->updateOrCreate(
                ['question' => $question, 'locale' => 'en'],
                [
                    'answer' => $answer,
                    'category' => $category,
                    'sort_order' => $order,
                    'is_published' => true,
                    'locale' => 'en',
                ],
            );
        }
    }
}
