# Qibla Finder

A modern Laravel website for **Qibla direction** and **Islamic prayer times**, with a live circular compass, worldwide place search, light/dark theme, 18 languages, and a full admin CMS.

Live-style features match a consumer Qibla app: geodesic bearing to the Kaaba (Masjid al-Haram), device compass heading, distance, OpenStreetMap, Hijri date, and configurable salah methods.

**Repository:** [github.com/Mehboob-alam1/Qibla-finder](https://github.com/Mehboob-alam1/Qibla-finder)

## Features

- **Full circular compass** — gold bezel, 360° ticks, cardinals, Kaaba marker, live heading and Qibla in the hub
- **Location permission** — the compass asks for GPS on load; overlay until access is granted
- **Any place on Earth** — search cities, towns, and named locations worldwide (OpenStreetMap Nominatim), plus a built-in city list
- **Prayer times** — Fajr, Dhuhr, Asr, Maghrib, Isha plus Imsak, sunrise, midnight; next-prayer countdown; monthly table; Hijri date
- **Calculation methods** — MWL, ISNA, Egypt, Umm al-Qura (Makkah), Karachi, Tehran, Jafari, Dubai; Standard or Hanafi Asr
- **Light / dark theme** — persisted in the browser
- **18 languages** — English, Arabic, Urdu, Persian, Indonesian, Malay, Turkish, French, German, Spanish, Italian, Dutch, Portuguese, Russian, Bengali, Hindi, Danish, Swedish (Arabic, Urdu, Persian are RTL)
- **Admin panel** — settings, CMS pages, guides, FAQs, contact inbox, users, traffic stats
- **PWA-ready** — web manifest and SVG favicon

## Stack

Laravel 13 · PHP 8.3+ · SQLite (or MySQL/PostgreSQL) · Tailwind CSS 4 · Vite 8 · Leaflet

## Local setup

Requirements: PHP 8.3+, Composer, Node.js 20+.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

For hot reload during UI work, run `npm run dev` in a second terminal instead of `npm run build`.

### Admin

| | |
| --- | --- |
| URL | `/admin/login` |
| Email | `admin@qiblafinder.test` |
| Password | `password` |

Change this password before any public deploy.

## Usage notes

- **iPhone Safari:** enable **Settings → Safari → Motion & Orientation Access** for the live compass.
- Desktop browsers often have no magnetometer; use the numeric Qibla bearing, the map, or a phone.
- Compass heading depends on the device sensor; the Kaaba bearing is computed from coordinates (geodesic initial heading).
- Location search uses [Nominatim](https://nominatim.openstreetmap.org/) and is cached. Respect their [usage policy](https://operations.osmfoundation.org/policies/nominatim/) in production.

## Production

- Set `APP_URL`, `APP_ENV=production`, and a real `APP_KEY`
- Use MySQL or PostgreSQL for real traffic
- Run `npm run build` and serve the `public/` directory
- Do not commit `.env`

## Deploy on Hostinger

Hostinger Git **does not run** `composer` or `npm`. This repo is set up so you can connect GitHub in hPanel and then finish one SSH (or hPanel terminal) command.

### 1. Connect the GitHub repo (required — in your Hostinger account)

1. Open [hPanel](https://hpanel.hostinger.com) → **Websites** → your site → **Dashboard**
2. Sidebar: **Advanced** → **Git**
3. Click **Connect with GitHub** (or **Continue with GitHub**)
4. Authorize the Hostinger GitHub App and grant access to **`Mehboob-alam1/Qibla-finder`**
5. Choose:
   - **Repository:** `Mehboob-alam1/Qibla-finder`
   - **Branch:** `main`
   - **Deploy directory:** `public_html` (empty this folder first if Git refuses a non-empty directory)
6. Click **Deploy**

Every later push to `main` auto-deploys.

### 2. PHP and database

In hPanel:

- Set **PHP** to **8.3** or newer
- Create a **MySQL** database and user
- Copy `.env.hostinger.example` to `.env` on the server (File Manager) and fill `APP_URL`, `APP_KEY` (or generate it in the next step), and the MySQL details

### 3. Install Laravel on the server

SSH (Advanced → **SSH Access**, often port **65002**):

```bash
cd ~/domains/YOUR-DOMAIN/public_html
# or: cd ~/public_html
bash scripts/hostinger-setup.sh
```

The site is served from `public/` via the root `.htaccess` (normal Hostinger `public_html` document root).

### 4. Admin

Change the seeded password immediately:

- URL: `https://YOUR-DOMAIN/admin/login`
- Email: `admin@qiblafinder.test`
- Password: `password`

## License

MIT
