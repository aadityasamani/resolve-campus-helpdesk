# Deployment record and next steps

## Actual local work performed

```powershell
php artisan key:generate
php artisan migrate --seed --force
php -d extension=openssl vendor/phpunit/phpunit/phpunit --colors=never
```

OpenSSL is enabled per invocation because this computer's default PHP CLI does not load it. Production must have Laravel's required PHP extensions enabled. `Launch.cmd` runs PHP's local development server with OpenSSL enabled. It is not the production deployment.

## SSH deployment template — NOT EXECUTED YET

Replace every placeholder using the approved host's actual settings. Confirm PHP 8.2+ and Laravel's required extensions are available. Public document root must be the `public` directory, never the project root. Use HTTPS.

```sh
ssh USER@HOST
git clone REPOSITORY_URL resolve
cd resolve
composer install --no-dev --prefer-dist --optimize-autoloader
cp .env.example .env
# Edit .env using the host's real absolute database path, HTTPS URL and production settings.
# APP_ENV=production; APP_DEBUG=false; SESSION_SECURE_COOKIE=true
touch database/database.sqlite
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan optimize
```

The production seeder creates categories only. It does not create the local demo users. Register your own account, then promote the intended staff account via a server-side Artisan/Tinker session, never a public form. Keep passwords private. Configure the host's web server for the `resolve/public` directory and ensure `storage`, `bootstrap/cache` and the SQLite directory have appropriate owner write access; do not use blanket `777` permissions.

A private GitHub repository needs a read-only deploy key on the hosting server. No GitHub token belongs in source code or command logs.

## Required subsequent update — NOT EXECUTED YET

After the initial site is live, change the release version or another visible feature locally, test it, then:

```sh
# Local checkout
git add config/release.php
git commit -m "Update visible application release"
git push origin main
# Hosting server, after SSH login and from the resolve checkout
git pull --ff-only origin main
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan optimize
git rev-parse HEAD
```

Verify the new version in the live page footer and record the actual commit hash and live URL. This demonstration is incomplete until it has really happened.

## Hosting investigation, 22 October 2026

alwaysdata's marketing advertised registration without a card, but its actual registration page requests card validation. It is not the no-card option originally suggested.

serv00's public registration page currently has no card field and its official documentation describes SSH and PHP support. Accounts require manual approval, potentially up to 72 hours. Availability and successful Laravel deployment are not yet verified for this student.

- https://www.serv00.com/register-account/
- https://docs.serv00.com/Login/
- https://docs.serv00.com/PHP/

A college-provided SSH hosting account is another compliant option. A local server or tunnel does not by itself complete the required remote deployment and Git update evidence.
