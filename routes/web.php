<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PlaceSearchController;
use App\Http\Controllers\PrayerTimesController;
use App\Http\Controllers\SitemapController;
use App\Support\LocalizedPaths;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/kiblat-online', [HomeController::class, 'index'])->name('home.id');
Route::get('/kiblat', [HomeController::class, 'index'])->name('home.ms');
Route::get('/jadwal-sholat', [PrayerTimesController::class, 'index'])->name('prayer-times.id');
Route::get('/waktu-solat', [PrayerTimesController::class, 'index'])->name('prayer-times.ms');

foreach (LocalizedPaths::redirects() as $from => $to) {
    Route::redirect($from, $to, 301);
}
Route::get('/sitemap.xml', [SitemapController::class, 'xml'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/qibla.json', [HomeController::class, 'qibla'])->name('qibla.json');
Route::get('/places/search', [PlaceSearchController::class, 'search'])
    ->middleware('throttle:30,1')
    ->name('places.search');
Route::get('/prayer-times', [PrayerTimesController::class, 'index'])->name('prayer-times');
Route::post('/prayer-times/calculate', [PrayerTimesController::class, 'calculate'])->name('prayer-times.calculate');
Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
Route::get('/qibla/{slug}', [CityController::class, 'qibla'])->where('slug', '[A-Za-z0-9\-]+')->name('cities.qibla');
Route::get('/prayer-times/{slug}', [CityController::class, 'prayer'])->where('slug', '[A-Za-z0-9\-]+')->name('cities.prayer');
Route::get('/offline', fn () => view('pages.offline'))->name('offline');
Route::get('/guides', [BlogController::class, 'index'])->name('blog.index');
Route::get('/guides/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/faq', [BlogController::class, 'faq'])->name('faq');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/locale/{locale}', [LocaleController::class, 'update'])->name('locale');
Route::get('/p/{slug}', [PageController::class, 'show'])->name('pages.show');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('login', [AdminAuthController::class, 'store'])->name('login.store');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'destroy'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('media', [AdminMediaController::class, 'store'])->name('media.store');
        Route::resource('pages', AdminPageController::class)->except('show');
        Route::resource('posts', AdminPostController::class)->except('show');
        Route::resource('faqs', AdminFaqController::class)->except('show');
        Route::get('messages', [ContactMessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [ContactMessageController::class, 'show'])->name('messages.show');
        Route::delete('messages/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');
        Route::resource('users', UserController::class)->except('show');
    });
});

Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('pages.flat');
