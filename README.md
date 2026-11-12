# eHub Academy Site

The eHub Academy landing page and content management system, built with Laravel 13 and Filament 5.

- **Public site:** a block-built homepage, insights (`/insights`), categories, tags, events (`/events`), search, `sitemap.xml` and `robots.txt`
- **Page builder:** pages are built from sections (hero, text, image + text, cards, statistics, latest insights, upcoming events, call-to-action banner, newsletter signup). Mark one page as the homepage. Nest pages under a parent to build the menu's dropdowns.
- **Pages:** admin-editable pages such as About or Services at `/{slug}`, optionally shown in the site menu
- **Events:** upcoming and past events with date, time, location, online link and registration link
- **Newsletter:** signup form in the footer and as a page section. Subscribers are listed in the admin and can be downloaded as CSV.
- **Branding:** primary and dark colours, logo, footer text and links are all set in **Site settings**
- **Contact form:** `/contact` saves messages to the admin inbox and emails the site's contact address, with spam protection (honeypot and rate limit)
- **Admin panel:** `/admin` for managing posts, categories, tags, pages, the inbox and site settings
- **Roles:** Super Admin, Admin, Editor, Author, Contributor, Moderator and Viewer (via `spatie/laravel-permission`)

## Requirements

- PHP 8.4+ with the `intl`, `mbstring`, `fileinfo`, `zip` and `pdo_mysql` extensions
- Composer 2
- Node.js 20+ and npm (local machine only, to build assets; not needed on the server)
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

Copy **`.env.production.example`** to `.env` on the server and replace every `CHANGE_ME` value. The key settings are:

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

The built front-end assets (`public/build`) are committed to git because the live server is shared hosting without Node.js. **Don't run npm on the server.** After changing anything in `resources/css` or `resources/js`, run `npm run build` locally and commit the updated `public/build`.

```bash
git pull origin main
composer install --no-dev --optimize-autoloader --no-interaction
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
- Contact form emails go to the **Contact email** in Site settings (or `MAIL_FROM_ADDRESS` if it's empty). They're queued, so a queue worker must be running and `MAIL_*` must point at a real mail server.
- `php artisan db:seed --force` creates a **draft** starter homepage, "Home", with example sections, once. Edit its text in **Site → Pages**, then publish it to make it the live homepage. Until then, `/` shows the latest insights. The same import groups the eHub pages into **About us**, **Solution** and **Methodology** dropdowns and adds a **Blogs** link to `/insights`; it is safe to re-run.
- The top menu is editable in **Site settings → Main menu**: choose published pages or custom links, reorder them, and add dropdown items under any main item. If the builder is empty, pages marked "Show in site menu" are used automatically.
- Footer link columns are editable in **Site settings → Footer & social**: each column has its own title and list of page links or custom links. The old Explore/Pages fields remain as a fallback when no columns are built.
- Old `/posts/...` links permanently redirect to `/insights/...`.
- Page addresses can't reuse paths the site already has, such as `admin`, `posts` or `contact`. See `Page::RESERVED_SLUGS`.
- `robots.txt` and `sitemap.xml` are generated from `APP_URL`, so don't add a static `public/robots.txt`.
