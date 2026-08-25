# Hanova WordPress Website

## Local URLs

- Website: http://localhost/clinic/
- Dashboard: http://localhost/clinic/wp-admin/

Start Apache and MySQL from XAMPP before opening either URL.

## Website settings

Open `Appearance > Customize > Clinic details` to configure:

- Doctor and clinic photos
- Phone, WhatsApp, and Instagram
- Booking and mobile app links
- Booking and app download URLs
- Hanova API base URL

The production API default is
`https://hanova-api-production.up.railway.app/api`.

The theme reads `/home`, which is the same endpoint used by the Hanova mobile
home screen. It renders the active offer, current care categories, product
names, images, prices, and currency directly from the Laravel dashboard. API
images with relative `/storage/...` paths are resolved against the Railway
domain automatically.

Home data is cached for five minutes per language. If the API is unavailable,
the site renders local product placeholders instead of blocking the page.

## Languages

The public website supports Arabic and English with the header language switch.
Theme content and layout direction change automatically between RTL and LTR.

## Production checklist

- Keep `wp-config.php` local and set the production `WP_HOME` / `WP_SITEURL` there.
- Use a supported production PHP and MySQL/MariaDB version.
- Change the local administrator password.
- Add the final booking, mobile app, Instagram, and privacy-policy links in the Customizer.
- Upload optimized WebP doctor, clinic, and before/after images.
- Disable debugging in `wp-config.php`.
- Configure HTTPS, backups, caching, SMTP, and security hardening.

## Git scope

Only the custom theme in `wp-content/themes/hanan-clinic`, this setup guide,
and the WordPress-specific `.gitignore` are versioned. WordPress core,
plugins, uploads, databases, and `wp-config.php` remain local so no credentials
or runtime data are pushed to GitHub.

## Railway deployment

The `Dockerfile` uses the official WordPress Apache image and copies only the
Hanova theme. Railway must run it as a separate service with this directory as
the root directory: `wordpress-clinic`.

Create a dedicated Railway MySQL service and set these variables on the
WordPress service using Railway service references:

- `WORDPRESS_DB_HOST=${{MySQL.MYSQLHOST}}:${{MySQL.MYSQLPORT}}`
- `WORDPRESS_DB_NAME=${{MySQL.MYSQLDATABASE}}`
- `WORDPRESS_DB_USER=${{MySQL.MYSQLUSER}}`
- `WORDPRESS_DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}`
- `WORDPRESS_CONFIG_EXTRA` with the public domain, HTTPS, and WordPress
  hardening constants.

Attach a Railway volume at `/var/www/html/wp-content/uploads` before uploading
any media. This keeps product-independent WordPress uploads between deployments.
