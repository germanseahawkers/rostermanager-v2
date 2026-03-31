# RosterManager v2

`RosterManager v2` is a lightweight PHP/MySQL roster cut simulator for NFL fan projects.

It is currently published by `German Sea Hawkers e.V.` and developed by Simon Kell. The app is built around a public Seahawks cutdown simulator, a protected admin backend, direct ESPN roster sync, and link-based sharing with club branding.

## Features

- Public 90-to-53 roster simulator
- Team and club branding via `.env`
- Localized UI: German, English, Spanish, French, Portuguese
- Personalized roster sharing with short links
- Social-preview-ready share pages
- Protected admin backend for player management
- Manual player CRUD
- Direct ESPN import from the admin area
- Automated ESPN import via CLI and cron
- Optional local player image hosting

## Stack

- PHP 8.2+
- MySQL or MariaDB
- PDO
- Server-rendered PHP views
- Session-based admin authentication
- Small vanilla JavaScript for simulator interactivity

There is no full framework, no frontend build step, and no Node dependency for production.

## Repository layout

- [`public/`](public/) web root
- [`src/`](src/) controllers, core classes, repositories, views, helpers
- [`config/`](config/) app, team and branding configuration
- [`database/`](database/) schema and migration helpers
- [`docs/architecture.md`](docs/architecture.md) architecture overview
- [`docs/import.md`](docs/import.md) roster import workflows
- [`scripts/import_roster.py`](scripts/import_roster.py) optional local ESPN-to-CSV helper
- [`scripts/import_espn.php`](scripts/import_espn.php) CLI ESPN sync entrypoint

## Setup

1. Point your web root to [`public/`](public/).
2. Create a database and import [`database/schema.sql`](database/schema.sql).
3. Copy [`.env.example`](.env.example) into your own environment configuration.
4. Make sure your web server routes requests through [`public/index.php`](public/index.php).
5. Ensure PHP can write to `public/uploads/players/` if you want local image hosting.

For existing installations, run the relevant migration files in [`database/`](database/) before deploying newer versions.

## Configuration

Most project-specific values come from `.env`.

Important app settings include:

- `APP_NAME`
- `APP_BASE_PATH`
- `DB_*`
- `ADMIN_USERNAME`
- `ADMIN_PASSWORD_HASH`

Important team settings include:

- `TEAM_NAME`
- `TEAM_CITY`
- `TEAM_SLUG`
- `TEAM_ESPN_ID`
- `TEAM_COLOR_PRIMARY`
- `TEAM_COLOR_SECONDARY`
- `TEAM_COLOR_SURFACE`
- `TEAM_COLOR_SURFACE_ALT`
- `TEAM_COLOR_TEXT`
- `TEAM_COLOR_INK`
- `TEAM_COLOR_MUTED`
- `TEAM_COLOR_LINE`

Important club branding settings include:

- `CLUB_NAME`
- `CLUB_LOGO_PATH`
- `CLUB_URL`

The simulator uses the team layer for NFL/team branding and the club layer for the publishing fan club.

## Simulator and sharing

The public simulator lets visitors:

- choose their final 53-man roster
- personalize it with a custom name
- switch between derived palette variants
- create a short share link only when they actively share

The share flow is database-backed:

- the simulator sends the current selection, locale, author name and palette to `POST /share/create`
- the backend stores or reuses a short token
- share links use `?s=TOKEN`
- opening the simulator with a share token restores the saved roster and personalization

The share page also exposes Open Graph and Twitter metadata so shared links generate rich previews on social platforms.

## Admin backend

The admin backend currently supports:

- login/logout
- player CRUD
- CSV import
- CSV + ZIP import for local headshots
- direct ESPN import from the browser

The player model currently uses:

- `id`
- `name`
- `position`
- `experience`
- `weight_kg`
- `height_cm`
- `image`
- `ordering`

`ordering` is only a manual display-order field. Imports intentionally use spaced values like `10, 20, 30` so later manual adjustments are easier.

## ESPN import

There are three practical ways to keep the roster up to date:

### 1. Admin ESPN import

This is the easiest workflow.

In the admin area, enter an ESPN team ID such as `26` for the Seahawks and optionally enable local image download. PHP then:

- fetches the ESPN roster API
- normalizes players into the internal model
- syncs by ESPN player ID
- updates existing players
- creates new players
- removes players no longer present in the roster
- preserves manual ordering for existing players

If local image download is enabled, player photos are stored under `public/uploads/players/`.

### 2. Automated CLI import

You can run the same sync from the command line:

```bash
php scripts/import_espn.php
```

Optional flags:

```bash
php scripts/import_espn.php --team-id=26 --download-images
```

This is intended for cron-based offseason syncs.

### 3. Local CSV preparation

If you still want a local preprocessing step, the Python helper can build a CSV and optional image ZIP:

```bash
python3 scripts/import_roster.py --team-slug seahawks --local-images
```

That workflow is documented in [`docs/import.md`](docs/import.md).

## Local development notes

- `php -l` is enough for quick PHP syntax checks
- `node --check public/assets/simulator.js` is useful for a fast JS syntax check
- the app can run on classic hosting without Node or Composer-based runtime requirements

## Default admin login

- Username: `admin`
- Development password fallback: `admin123`

For production, set your own `ADMIN_PASSWORD_HASH` and do not rely on the fallback password.

## Documentation

- [`docs/architecture.md`](docs/architecture.md)
- [`docs/import.md`](docs/import.md)

## License and asset notice

The source code in this repository is licensed under the [MIT License](LICENSE) and attributed to `German Sea Hawkers e.V.`.

That license does not automatically apply to third-party names, logos, trademarks, player images, or imported external assets. If you publish or redistribute a public instance, make sure you have the necessary rights for all non-code materials.
