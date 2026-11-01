# Muhame Site

The Muhame website and content management system, built with Laravel 13 and Filament 5.

- **Public site:** home page, posts, categories, tags, search, `sitemap.xml` and `robots.txt`
- **Admin panel:** `/admin` for managing posts, categories, tags and site settings
- **Roles:** Super Admin, Admin, Editor, Author, Contributor, Moderator and Viewer (via `spatie/laravel-permission`)

## Requirements

- PHP 8.4+ with the `intl`, `mbstring`, `fileinfo`, `zip` and `pdo_mysql` extensions
- Composer 2
- Node.js 20+ and npm (only needed to build assets)
- MySQL 8 or MariaDB 10.6+

## Local development

```bash
composer setup                 # installs deps, creates .env, generates key, migrates, builds assets
php artisan db:seed            # creates roles, permissions and default site settings
php artisan storage:link       # serves uploaded images
php artisan cms:create-admin you@example.com --name="Your Name"
composer dev                   # runs the server, queue worker, logs and Vite together
```

Then sign in at http://localhost:8000/admin.

Run the tests with `composer test`, and check code style with `vendor/bin/pint`.

## Production deployment

### 1. Environment

Copy `.env.example` to `.env` on the server and set at least these values:

```dotenv
APP_NAME=Muhame
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
LOG_LEVEL=warning

DB_DATABASE=...
DB_USERNAME=...           # a dedicated user, not root
DB_PASSWORD=...

SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

MAIL_MAILER=smtp          # plus MAIL_HOST / MAIL_USERNAME / MAIL_PASSWORD
MAIL_FROM_ADDRESS=hello@your-domain.com

CMS_ALLOW_REGISTRATION=false
```

Run `php artisan key:generate` once on the first deploy, and never change the key afterwards.

### 2. Deploy steps

```bash
composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build
php artisan migrate --force
php artisan db:seed --force          # safe to re-run; syncs roles and permissions
php artisan storage:link             # first deploy only
php artisan optimize                 # caches config, routes, views and events
php artisan filament:optimize        # caches Filament components and icons
```

On the first deploy, create the first administrator:

```bash
php artisan cms:create-admin owner@your-domain.com --name="Site Owner"
```

The same command resets the password of an existing user and makes them a Super Admin.

### 3. Server

- Point the web server's document root at `public/`.
- `storage/` and `bootstrap/cache/` must be writable by the web server user.
- Serve the site over HTTPS. In production, the app generates `https://` URLs and refuses destructive database commands such as `migrate:fresh` and `db:wipe`.
- Run a queue worker (Supervisor or systemd): `php artisan queue:work --tries=3`.
- Add the scheduler to cron: `* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1`.

## Notes

- Public sign-up for the admin panel is disabled. It can only be enabled in a `local` environment with `CMS_ALLOW_REGISTRATION=true`.
- Post HTML is sanitized before it is shown on the public site, and navigation and social links are restricted to safe URLs.
- `robots.txt` and `sitemap.xml` are generated from `APP_URL`, so don't add a static `public/robots.txt`.
