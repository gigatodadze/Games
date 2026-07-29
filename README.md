# ალიასი

Georgian team-based Alias game built with Laravel 13 and NativePHP Mobile 3.

## Game

- Play with 2–4 teams on one phone.
- Explain the displayed word without saying it or using the same root.
- Choose a 45, 60, or 90 second turn and a Georgian offline word deck.
- Correct answers earn one point; skipped words can cost zero or one point.
- Review and correct every answer before passing the phone to the next team.
- End after a fixed number of rounds or when the target score is reached.

## Local browser

```bash
composer install
npm install
npm run build
php artisan serve
```

## iOS

The NativePHP application identifier is `ge.namiokobana.game`. Configure
`NATIVEPHP_DEVELOPMENT_TEAM` in `.env` only when running on a physical device.

```bash
php artisan native:install ios
npm run build -- --mode=ios
php artisan native:run ios
```

If Xcode reports `resource fork, Finder information, or similar detritus not
allowed`, the checkout is inside an iCloud/FileProvider-managed folder. Move it
to a regular local development directory (for example `~/Developer`) and run
the iOS command again.

## TestFlight

TestFlight requires an active Apple Developer Program team, access to
Certificates, Identifiers & Profiles, an App Store distribution certificate,
an App Store provisioning profile, and an App Store Connect API key.

Generate the branded app icon and launch screens, then build the App Store package with:

```bash
php scripts/generate-app-icon.php
npm run build -- --mode=ios
php artisan native:package ios --export-method=app-store
```

Set `APP_ENV=production` and `APP_DEBUG=false` in `.env` before creating the
package.

Add `--upload-to-app-store` after the App Store Connect credentials have been
configured. Keep those credentials in `.env`; the release bundler removes
`APP_STORE_*` and `IOS_*` values from the packaged application.
