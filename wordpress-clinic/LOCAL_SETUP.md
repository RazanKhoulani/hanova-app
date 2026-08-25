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
