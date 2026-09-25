# Hostinger MySQL — full site database

Everything editable in the admin (guides, pages, FAQs, settings, contact inbox, users) lives in **MySQL**. Git deploy only updates **code**; it does not replace your database.

---

## 1. Create MySQL in hPanel

1. [hPanel](https://hpanel.hostinger.com) → **Websites** → your site → **Dashboard**
2. **Databases** → **Management** → **MySQL Databases**
3. **Create database** — note the **database name** (e.g. `u123456789_qibla`)
4. **Create user** with a strong password — note **username**
5. **Add user to database** with **All privileges**

Hostinger also shows **MySQL hostname** on that screen. Use it in `.env`:

| hPanel shows | Put in `.env` |
|--------------|----------------|
| Hostname `127.0.0.1` or `localhost` | `DB_HOST=127.0.0.1` |
| Hostname like `mysql123.hostinger.com` | `DB_HOST=mysql123.hostinger.com` |

---

## 2. Create `.env` on the server

In **File Manager** or SSH, in your site root (`public_html` or `domains/…/public_html`):

1. Copy `.env.hostinger.example` → `.env`
2. Fill in:

```env
APP_URL=https://your-domain.com
APP_KEY=   # leave empty first — setup script generates it

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u123456789_qibla
DB_USERNAME=u123456789_qiblauser
DB_PASSWORD=your_strong_password
```

Do **not** commit `.env` to GitHub.

---

## 3. Run one-time setup (SSH)

**Advanced** → **SSH Access** → connect, then:

```bash
cd ~/domains/YOUR-DOMAIN/public_html
# or: cd ~/public_html

bash scripts/hostinger-setup.sh
```

This will:

- Check MySQL credentials
- Run **migrations** (creates all tables: users, posts, pages, settings, faqs, messages, …)
- **Seed** demo content only on first install (skipped if `storage/framework/installed` exists)
- Cache config/routes/views

Admin login after seed: `admin@qiblafinder.test` / `password` — **change immediately**.

---

## 4. After every code deploy

```bash
bash scripts/hostinger-post-deploy.sh
```

Runs new migrations only; **does not** wipe your CMS data.

---

## 5. If the site used SQLite before

Older visits may have created `database/database.sqlite` with your guides and CMS edits. **Move that data into MySQL** (do not re-seed):

```bash
cd ~/domains/YOUR-DOMAIN/public_html
bash scripts/hostinger-migrate-to-mysql.sh
```

Or manually:

```bash
php artisan site:import-sqlite --dry-run
php artisan site:import-sqlite
```

This copies **settings, users, pages, posts, FAQs, contact messages, and page views** into MySQL. A timestamped `.bak` copy of the SQLite file is created first.

After you verify the live site, delete `database/database.sqlite` so production never uses it again.

---

## 6. Backup

hPanel → **Backups** or **phpMyAdmin** → export your database regularly.

Tables include: `users`, `posts`, `pages`, `settings`, `faqs`, `contact_messages`, `page_views`, …
