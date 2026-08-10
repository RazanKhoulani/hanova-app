# Hanova Platform

Laravel API and administration dashboard for the Hanova Flutter application.

## Setup

```powershell
composer install
php artisan migrate --seed
php artisan serve --host=0.0.0.0 --port=8000
```

The administration dashboard is available at `/admin`. API routes remain under
`/api` so existing mobile integrations continue to work.
