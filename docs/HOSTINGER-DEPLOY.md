# Hostinger: fix `proc_open` / Composer deploy failure

Hostinger **Git** deploy always runs `composer install` in a **build container** where `proc_open` is often **disabled**. Changing website PHP in hPanel does **not** always change that build PHP.

**Vendored `vendor/` in Git does not skip that step** on the default Git pipeline.

---

## Solution 1 — Enable `proc_open` (keep Hostinger Git)

1. hPanel → **Websites** → **Dashboard** → **Advanced** → **PHP Configuration**
2. **PHP options** → **`disableFunctions`**
3. Remove **`proc_open`** → **Save**
4. Set **PHP 8.3+**
5. **Git** → **Redeploy**

If it **still** fails, the Git **build** environment is separate — open Hostinger live chat and ask: *“Enable proc_open for Git deployment / Composer build for my account.”*

---

## Solution 2 — GitHub FTP deploy (recommended if Git build keeps failing)

This repo ships **`.github/workflows/deploy-hostinger.yml`**. It builds on GitHub (proc_open works) and uploads **including `vendor/`** via FTP — **no Composer on Hostinger**.

### A. Add GitHub secrets

Repo → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**:

| Secret | Example |
|--------|---------|
| `HOSTINGER_FTP_HOST` | `ftp.qiblafinders.io` or Hostinger FTP hostname from hPanel |
| `HOSTINGER_FTP_USER` | FTP username |
| `HOSTINGER_FTP_PASSWORD` | FTP password |
| `HOSTINGER_FTP_PATH` | `/domains/qiblafinders.io/public_html/` or `/public_html/` |

(FTP path: hPanel → **Files** → note path to `public_html` where `.htaccess` and `artisan` live.)

### B. Turn off Hostinger Git auto-deploy

hPanel → **Advanced** → **Git** → disable **Auto deployment** (or remove the push webhook).

Otherwise every push runs **two** deploys: one fails (Composer), one may succeed (FTP).

### C. Push to `main`

Actions tab → **Deploy to Hostinger (FTP)** should succeed. Then once on SSH:

```bash
cd ~/domains/qiblafinders.io/public_html
bash scripts/hostinger-post-deploy.sh
```

---

## Solution 3 — SSH manual pull (no FTP secrets)

If the server already has a git clone and SSH access:

```bash
cd ~/domains/YOUR-DOMAIN/public_html
git fetch origin main && git reset --hard origin/main
bash scripts/hostinger-post-deploy.sh
```

Or run workflow **Deploy to Hostinger (SSH)** manually (needs `HOSTINGER_SSH_*` secrets).

---

## Solution 4 — Custom Git build command (if hPanel offers it)

Set build command to:

```bash
bash build.sh
```

(not `composer install`). Then redeploy. Requires `vendor/` in the repo (already committed).

---

## After any successful deploy

```bash
bash scripts/hostinger-post-deploy.sh
```

Runs migrations and caches; **does not** wipe CMS when `storage/framework/installed` exists.
