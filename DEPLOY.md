# Deploy — staging (FTP)

Staging runs on a plain **PHP 8.3+ / MySQL 8** FTP host — no Redis, Meilisearch, Docker, SSH or
queue worker. You upload the files by FTP and set the database up **once** by importing a SQL dump
through phpMyAdmin (the host has no shell to run `php artisan migrate`, and an HTTP endpoint that
ran migrations would be a remote-code-execution surface — so we don't expose one).

Upload by hand with any FTP client, or let GitHub Actions sync on every push to the `staging`
branch (`.github/workflows/deploy-staging.yml`).

---

## Where the "migrate logic" lives

The schema is defined by the Laravel migrations in **`database/migrations/`** and the data by the
seeders in **`database/seeders/`** (the 26 real events are in `VagaculturalSeeder`). For an FTP host
with no shell, that schema + seed is materialised into a single importable file:

- **`database/dumps/staging-seed.sql`** — full schema + seed data (gitignored; it lives on your
  machine). Re-generate it any time after changing migrations/seeders:
  ```sh
  docker compose exec -T db sh -lc 'mysqldump -u vaga -psecret --no-tablespaces --single-transaction vaga' > database/dumps/staging-seed.sql
  ```

---

## First deploy (one time)

1. **Create an empty MySQL database** on the host; note the name, user and password.
2. **Import `database/dumps/staging-seed.sql`** into it with **phpMyAdmin** → *Import* → choose the
   file → *Go*. This creates every table and loads the 26 real events (the dump drops/recreates
   tables, so it's safe to re-run).
3. **Upload the app** by FTP. The web root must be the app's **`public/`** folder — point the
   staging domain's document root there (e.g. docroot → `/staging/public`). If the host forces
   `public_html`, put the contents of `public/` in `public_html` and the rest of the app one level
   above, then fix the two `require` paths in `public_html/index.php`.
4. **Create `.env` on the server** from [`.env.staging.example`](.env.staging.example): fill the
   MySQL credentials and `APP_URL`, and set `APP_KEY`. Generate a key on your dev machine and paste
   it in:
   ```sh
   docker compose exec app php artisan key:generate --show
   ```
5. **Make `storage/` and `bootstrap/cache/` writable** (755/775).

That's it — the site is live with the real events. Cover images are referenced from the source CDN,
so no `storage:link` is needed for this dataset.

Requirements: PHP 8.3+ with `pdo_mysql, mbstring, intl, gd, zip, bcmath`; MySQL 8.

---

## Later deploys

Re-upload the changed files by FTP. If you changed **migrations or seeders**, re-generate the dump
(command above) and re-import it via phpMyAdmin — or, if your host offers a scheduled task / cron,
run `php artisan migrate --force` there.

### Optional: automate the file sync with GitHub Actions
Add the secrets `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`, `FTP_SERVER_DIR`, then:
```sh
git checkout -b staging      # first time
git merge main
git push origin staging      # ← builds assets and FTP-uploads (never touches the server .env)
```
Watch it under the **Actions** tab. The database still gets set up by the one-time SQL import above.

---

## Notes & troubleshooting

- **500 / blank page:** check `storage/logs/laravel.log` — usually a missing `APP_KEY`, wrong DB
  credentials, or `storage/` not writable.
- **Styles missing:** `public/build/` didn't upload, or `APP_URL` doesn't match the real URL.
- **Images:** event cover images are referenced from the source CDN and load from any public host.
- **No external services:** cache/session/queue run on the database; search uses Scout's in-process
  `collection` engine — nothing else to install.
