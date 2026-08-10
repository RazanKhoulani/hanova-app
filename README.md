# Hanova

Hanova is a bilingual beauty-care platform with a Flutter mobile application
and a Laravel API and administration dashboard.

## Repository

- `app/`: Flutter application for Android, iOS, web, and desktop.
- `laravel-medical-ecommerce/`: Laravel API, administration dashboard, and
  database migrations and seeders.

## Flutter

```powershell
cd app
flutter pub get
flutter run
```

## Laravel

```powershell
cd laravel-medical-ecommerce
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=0.0.0.0 --port=8000
```

Local environment files, credentials, generated builds, dependencies, and the
separate WordPress installation are intentionally excluded from this
repository.
