# Hostinger deploy: `proc_open` / Composer failed

If the deploy log shows:

```text
The Process class relies on proc_open, which is not available on your PHP installation.
```

Hostinger Git deploy runs **`composer install`** in a build step. Composer **requires** `proc_open`. On **new Hostinger accounts**, `proc_open` is in **`disableFunctions` by default**.

**Vendored `vendor/` does not disable that step.** Until `proc_open` is enabled (or you change/disable the Git build), every deploy will fail at step “Installing Composer dependencies”.

This repository includes a production **`vendor/`** folder so the live site does not need Composer **after** files are on the server.

## Fix A (recommended): enable `proc_open`

1. [hPanel](https://hpanel.hostinger.com) → **Websites** → your site → **Dashboard**
2. **Advanced** → **PHP Configuration**
3. Open the **PHP options** tab
4. Find **`disableFunctions`**
5. Remove **`proc_open`** from the list (leave other entries as Hostinger recommends)
6. **Save**
7. **Git** → **Redeploy** (deploy must show a **new** commit, not `66f2d69`)

Also set **PHP 8.3+** for the website and for Git/CLI if hPanel offers a separate version dropdown.

Official note: [How to enable disabled PHP functions](https://www.hostinger.com/support/3212034-how-to-enable-disabled-php-functions-in-hostinger/)

## Fix B: custom build command (skip Composer on Hostinger)

If hPanel **Git** lets you set a **custom build command**, use:

```bash
bash .hostinger/build.sh
```

Then redeploy. The script exits successfully when `vendor/autoload.php` is present in the repo.

## After a successful deploy

SSH once (if needed):

```bash
cd ~/domains/YOUR-DOMAIN/public_html
bash scripts/hostinger-setup.sh
```

That runs migrations and caches **without** wiping CMS data when `storage/framework/installed` exists.

## Fix C: GitHub Actions → FTP

This repo includes `.github/workflows/deploy-hostinger.yml`. It runs `composer install` and `npm run build` on GitHub, then uploads via FTP **including `vendor/`**.

1. Add GitHub repository secrets: `HOSTINGER_FTP_HOST`, `HOSTINGER_FTP_USER`, `HOSTINGER_FTP_PASSWORD`, `HOSTINGER_FTP_PATH`
2. Push to `main` or run the workflow manually
3. Turn off Hostinger **Git auto-deploy** if you use FTP only, to avoid a failed Composer step on every push
