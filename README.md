# ქართული თამაშები

Two Georgian team word games built with Laravel 13 and NativePHP Mobile 3.

## Games

- Choose **Alias** or **Namiokobana** from the main screen.
- Alias supports 2–4 teams, timed turns, offline Georgian word decks, answer review, and round- or score-based games.
- Namiokobana is played by two pairs: choose whether the opposing team enters the secret word or the app draws one automatically, then explain it using your agreed rules—spoken hints, gestures, or silent play.
- Both games keep the scoreboard and rotate teams with equal turns on one phone.

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
