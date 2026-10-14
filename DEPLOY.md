# Deploy — staging (FTP + GitHub Actions)

Automatic deploy to the FTP staging host. **Push to the `staging` branch** (or run the
workflow manually from the **Actions** tab) and CI builds the app, uploads it by FTP, and —
when SSH is configured — runs the migrations. No more dragging files over FTP by hand.

The pipeline lives in `.github/workflows/deploy-staging.yml`.

---

## What the pipeline does

1. Installs PHP deps for production (`composer install --no-dev --optimize-autoloader`).
2. Builds the front-end assets (`npm ci && npm run build`).
3. Uploads everything to the host by FTP (incremental — only changed files), **keeping the
   server's own `.env`** and never wiping the remote tree.
4. If SSH secrets are present, runs `php artisan migrate --force` and caches config/routes/views.

---

## 1. GitHub secrets (one time)

**Settings → Secrets and variables → Actions → New repository secret:**

| Secret | Value |
| --- | --- |
| `FTP_SERVER` | FTP host, e.g. `ftp.vagacultural.pt` |
| `FTP_USERNAME` | FTP user |
| `FTP_PASSWORD` | FTP password |
| `FTP_SERVER_DIR` | target dir on the host where the app lives, e.g. `/staging/` |

**Optional — enables fully-automatic migrations (recommended if the host has SSH):**

| Secret | Value |
| --- | --- |
| `SSH_HOST` | SSH host |
| `SSH_USERNAME` | SSH user |
| `SSH_KEY` | private key (the matching public key is authorised on the host) |
| `SSH_APP_PATH` | absolute path of the app on the host, e.g. `/home/user/staging` |

Leave the SSH secrets out and the migrate step is skipped — run migrations manually (below).

---

## 2. One-time host setup

1. **Point the staging domain/subdomain's document root to the app's `public/` folder**
   (e.g. docroot → `/staging/public`). This is the single most important step for Laravel.
   If the host forces `public_html` and can't be changed, put the contents of `public/` in
   `public_html` and the rest of the app one level above, and edit the two `require` paths in
   `public_html/index.php`.
2. **Create `.env` on the server** from [`.env.staging.example`](.env.staging.example), filling
   the MySQL credentials and `APP_URL`. Generate the key once with `php artisan key:generate --show`
   and paste it into `APP_KEY`.
3. **Writable dirs:** make `storage/` and `bootstrap/cache/` writable (755/775).
4. **Storage symlink:** `php artisan storage:link` (once).
5. **First database load** (creates tables + the 26 real demo events):
   ```sh
   php artisan migrate:fresh --seed --force
   ```
   Steps 2–5 need a shell on the host (SSH or the control-panel terminal). On a truly FTP-only
   host, use the control panel's "Terminal"/cron once, or a host that offers SSH.

Requirements on the host: **PHP 8.3+** with `pdo_mysql, mbstring, intl, gd, zip, bcmath`, and **MySQL 8**.
No Redis, Meilisearch, Docker or queue worker is needed — staging runs cache/session/queue on the
database and search with Scout's in-process `collection` engine.

---

## 3. Deploying

```sh
git checkout staging        # create it from main the first time: git checkout -b staging
git merge main              # bring in what you want to ship
git push origin staging     # ← this triggers the deploy
```

Watch it under the repo's **Actions** tab. Subsequent pushes re-sync only changed files.

### Migrations after the first load
- **With SSH secrets:** CI runs `php artisan migrate --force` automatically on every deploy
  (additive only — it never wipes data).
- **Without SSH:** run `php artisan migrate --force` yourself on the host after a deploy that
  adds migrations. To reset staging back to the real demo data, run
  `php artisan migrate:fresh --seed --force`.

---

## Troubleshooting

- **500 / blank page:** check `storage/logs/laravel.log`; usually a missing `APP_KEY`, wrong DB
  credentials, or `storage/` not writable.
- **Styles missing:** the asset build didn't upload — confirm `public/build/` is present on the
  host and `APP_URL` matches the real URL.
- **Images don't load:** event cover images are referenced from the source CDN; they need outbound
  access from the browser (they will load fine on any public host).
- **"No application encryption key":** set `APP_KEY` in the server `.env`.
