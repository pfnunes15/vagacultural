# Deploy — staging (SSH + GitHub Actions)

Automatic deploy to the staging host over SSH. **Push to the `staging` branch** (or run the
workflow manually from the **Actions** tab) and CI builds the app, syncs it with `rsync`, and runs
the migrations — all in one SSH session. No more dragging files over FTP by hand.

The pipeline lives in `.github/workflows/deploy-staging.yml`.

---

## What the pipeline does

1. Installs PHP deps for production (`composer install --no-dev --optimize-autoloader`).
2. Builds the front-end assets (`npm ci && npm run build`).
3. `rsync`s the app to the host over SSH (only changed files; `.env` and runtime dirs are never
   touched — see `.deployignore`).
4. Runs `php artisan migrate --force` and caches config/routes/views over SSH.

---

## 1. GitHub secrets (one time)

**Settings → Secrets and variables → Actions → New repository secret:**

| Secret | Value |
| --- | --- |
| `SSH_HOST` | host, e.g. `staging.vagacultural.pt` |
| `SSH_USERNAME` | SSH user |
| `SSH_KEY` | private key — its **public** key must be in the host's `~/.ssh/authorized_keys` |
| `SSH_APP_PATH` | absolute path of the app on the host, e.g. `/home/user/staging` |
| `SSH_PORT` | *(optional)* SSH port, defaults to `22` |

Generate a dedicated deploy key: `ssh-keygen -t ed25519 -f deploy_key -C "gh-actions staging"`,
put `deploy_key` in the `SSH_KEY` secret and append `deploy_key.pub` to the host's
`~/.ssh/authorized_keys`.

---

## 2. One-time host setup

1. **Point the staging domain/subdomain's document root to the app's `public/` folder**
   (e.g. docroot → `/home/user/staging/public`). This is the single most important step for Laravel.
2. **Create `.env` on the server** from [`.env.staging.example`](.env.staging.example), filling the
   MySQL credentials and `APP_URL`. Generate the key once with `php artisan key:generate --show`
   and paste it into `APP_KEY`.
3. **Writable dirs:** `storage/` and `bootstrap/cache/` writable (755/775).
4. **Storage symlink:** `php artisan storage:link` (once).
5. **First database load** (creates tables + the 26 real demo events):
   ```sh
   php artisan migrate:fresh --seed --force
   ```

Requirements on the host: **PHP 8.3+** with `pdo_mysql, mbstring, intl, gd, zip, bcmath`, and
**MySQL 8**. No Redis, Meilisearch, Docker or queue worker is needed — staging runs cache/session/
queue on the database and search with Scout's in-process `collection` engine.

---

## 3. Deploying

```sh
git checkout staging        # create it from main the first time: git checkout -b staging
git merge main              # bring in what you want to ship
git push origin staging     # ← this triggers the deploy
```

Watch it under the repo's **Actions** tab. Every push re-syncs only changed files and runs
`php artisan migrate --force` (additive only — it never wipes data).

To reset staging back to the real demo data, run `php artisan migrate:fresh --seed --force` on the
host once.

---

## Troubleshooting

- **500 / blank page:** check `storage/logs/laravel.log`; usually a missing `APP_KEY`, wrong DB
  credentials, or `storage/` not writable.
- **Styles missing:** the asset build didn't sync — confirm `public/build/` exists on the host and
  `APP_URL` matches the real URL.
- **Host key verification failed:** the first run adds the host to `known_hosts` via `ssh-keyscan`;
  if the host uses a non-standard port, set the `SSH_PORT` secret.
- **Images don't load:** event cover images are referenced from the source CDN; they load fine from
  any public host with outbound access.
