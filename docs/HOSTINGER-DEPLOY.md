# Hostinger deploy: `proc_open` / Composer failed

If the deploy log shows:

```text
The Process class relies on proc_open, which is not available on your PHP installation.
```

Hostinger’s Git build runs `composer install`. Composer **requires** `proc_open`. On **new Hostinger accounts**, `proc_open` is in **`disableFunctions` by default**.

## Fix (recommended): enable `proc_open`

1. [hPanel](https://hpanel.hostinger.com) → **Websites** → your site → **Dashboard**
2. **Advanced** → **PHP Configuration**
3. Open the **PHP options** tab
4. Find **`disableFunctions`**
5. Remove **`proc_open`** from the list (leave other entries as Hostinger recommends)
6. **Save**
7. **Git** → **Redeploy** (or push to `main` again)

Also set **PHP 8.3+** for the website and for Git/CLI if hPanel offers a separate version dropdown.

Official note: [How to enable disabled PHP functions](https://www.hostinger.com/support/3212034-how-to-enable-disabled-php-functions-in-hostinger/)

## After a successful deploy

SSH once (if needed):

```bash
cd ~/domains/YOUR-DOMAIN/public_html
bash scripts/hostinger-setup.sh
```

That runs migrations and caches **without** wiping CMS data when `storage/framework/installed` exists.

## Alternative: GitHub Actions → FTP

This repo includes `.github/workflows/deploy-hostinger.yml`. It runs `composer install` and `npm run build` on GitHub (where `proc_open` works), then uploads via FTP **including `vendor/`**.

1. Add GitHub repository secrets: `HOSTINGER_FTP_HOST`, `HOSTINGER_FTP_USER`, `HOSTINGER_FTP_PASSWORD`, `HOSTINGER_FTP_PATH`
2. Run the workflow (or push to `main` if auto-deploy is enabled)
3. Turn off Hostinger **Git auto-deploy** if you use FTP only, to avoid a failed Composer step on every push

## Alternative: vendored `vendor/` in Git

If Hostinger lets you set a **custom build command**, use:

```bash
bash scripts/hostinger-build.sh
```

and commit the production `vendor/` folder (from `composer install --no-dev --optimize-autoloader` locally). Only use this if you cannot enable `proc_open` and do not use FTP deploy.
