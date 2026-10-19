# Google Search Console cleanup (after deploy)

This site now exposes a **small** indexable URL budget:

- Home (compass) + localized `/kiblat-online`, `/kiblat`
- `/cities` hub
- Curated `/qibla/{slug}` pages (`is_indexable` in `config/qibla.php`, currently ~120)
- Contact + About / Privacy / Terms

Removed URLs return **410 Gone** (`/guides*`, `/faq*`, `/duas-qibla*`) or **301** (prayer city pages → `/qibla/{slug}`, standalone prayer → home).

Do **not** Disallow removed URLs in `robots.txt` — Google must crawl them to see 410/301.

---

## A. Sitemap

1. Open [Google Search Console](https://search.google.com/search-console) → property for `qiblafinders.io`.
2. **Sitemaps** → remove any old sitemap URLs that are not `https://qiblafinders.io/sitemap.xml`.
3. Submit: `https://qiblafinders.io/sitemap.xml`
4. Wait for processing; confirm URL count is roughly **~2,200–2,300** (18 locales × ~126 paths: home + cities + ~120 cities + contact + legal), not tens of thousands.

## B. Removals / indexing

1. **Page indexing** → filter “Crawled – currently not indexed” / “Excluded” over time; 410s should leave the index.
2. For stubborn guide URLs still listed as **Indexed**:
   - Use **Removals** → Temporary removals only if urgent (lasts ~6 months).
   - Prefer letting **410** + new sitemap do the work (usually days–weeks).
3. Request indexing on a few **canonical** URLs after deploy (home, `/cities`, 2–3 top city pages) via URL Inspection → **Request indexing**.

## C. Bing Webmaster Tools

1. Submit the same `https://qiblafinders.io/sitemap.xml`.
2. Use URL submission for the homepage if needed.

## D. After Hostinger deploy (SSH)

```bash
cd ~/domains/qiblafinders.io/public_html
git pull origin main   # or your deploy method
bash scripts/hostinger-post-deploy.sh
```

Confirm:

```bash
curl -sI https://qiblafinders.io/guides | head -5          # expect 410
curl -sI https://qiblafinders.io/prayer-times/london | head -5  # expect 301 → /qibla/london
curl -s https://qiblafinders.io/sitemap.xml | grep -c '<loc>' # budget-sized count
```

## E. Raising / lowering city count

Edit `config/qibla.php` → each city has `'is_indexable' => true|false`.  
Set `false` to drop a city from the sitemap and public city hub; requests to `/qibla/{slug}` 301 to the nearest indexable city (or home).

Current whitelist: **120 indexable** (+ a few non-indexable kept for tool search / nearest redirects).  
Ceiling: **~120 cities × 18 locales** plus hub/legal/home (~2,268–2,300 `<loc>` entries).
