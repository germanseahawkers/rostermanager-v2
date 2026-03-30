# rostermanager-v2

`rostermanager-v2` is a lean PHP/MySQL roster manager for NFL fan projects.

The current product focus is a team-branded 90-to-53 roster cut simulator with a lightweight admin backend for managing players and importing roster data.

Published by German Sea Hawkers e.V. and developed by Simon Kell.

## What it does

- Public 53-man roster simulator
- Team-configurable branding and position groups
- German and English UI
- Shareable roster URLs
- Server-rendered SVG share card
- Protected admin area
- Player CRUD
- Direct ESPN roster import from admin
- CSV roster import
- Optional local player image import via ZIP

## Stack

- PHP 8.2+
- MySQL or MariaDB
- PDO
- Server-rendered views
- Session-based admin authentication
- Small custom app structure instead of a full framework

This keeps deployment simple for classic hosting environments such as Plesk while still preserving clean separation between routing, controllers, repositories and helpers.

## Project structure

- [`public/`](public/) public web root
- [`src/`](src/) application code
- [`config/`](config/) app and team configuration
- [`database/schema.sql`](database/schema.sql) database schema
- [`docs/architecture.md`](docs/architecture.md) architecture notes
- [`docs/import.md`](docs/import.md) roster import workflow
- [`scripts/import_roster.py`](scripts/import_roster.py) ESPN roster importer

## Team configuration

Team-specific values live in [`config/team.php`](config/team.php) and can also be overridden through [`.env.example`](.env.example).

Key settings include:

- team name, city, nickname and slug
- color palette
- tagline
- logo path
- roster limit
- simulator position-group mapping

That makes the project reusable for other teams without changing the core simulator logic.

## Setup

1. Point your web root to [`public/`](public/).
2. Create a database and import [`database/schema.sql`](database/schema.sql).
3. Copy settings from [`.env.example`](.env.example) into your environment.
4. Make sure Apache `mod_rewrite` is enabled so [`public/.htaccess`](public/.htaccess) can route requests through `index.php`.
5. Ensure PHP can write uploaded player images into `public/uploads/players/`.

If you already have an existing installation based on the older schema, run [`database/migrate_players_to_metric_columns.sql`](database/migrate_players_to_metric_columns.sql) once before deploying this version.

## Local roster import

The importer pulls data from the ESPN NFL roster API and writes a CSV that can be uploaded in the admin backend.

Default Seahawks import:

```bash
python3 scripts/import_roster.py
```

Using a team slug:

```bash
python3 scripts/import_roster.py --team-slug seahawks
```

Generating local image assets as well:

```bash
python3 scripts/import_roster.py --team-slug seahawks --local-images
```

With `--local-images`, the script:

- downloads player headshots
- writes local image filenames into the CSV
- creates a matching ZIP archive with all downloaded images

Generated files are written into [`database/imports/`](database/imports/) by default.

More details are documented in [`docs/import.md`](docs/import.md).

## Admin import workflow

The admin backend now supports two roster-maintenance paths:

1. Direct ESPN import
   Enter an ESPN team ID in the admin area and let PHP fetch, normalize and sync the roster directly.
2. CSV import
   Use this when you prefer a local preprocessing step or want to upload a CSV and optional ZIP manually.

### Direct ESPN import

The built-in ESPN import is the simplest offseason workflow:

- enter an ESPN team ID such as `26` for the Seahawks
- optionally enable local player image download
- run the import directly in the admin backend

The backend then:

- fetches the roster from ESPN
- maps the response to the internal player model
- syncs by ESPN player ID
- updates existing players
- creates new players
- deletes players no longer present in the ESPN roster
- preserves existing manual ordering for updated players

If image download is enabled, player headshots are stored locally in `public/uploads/players/`.

### Automated ESPN import via cron

You can run the same sync from the command line and schedule it with cron:

```bash
php scripts/import_espn.php
```

Optional flags:

```bash
php scripts/import_espn.php --team-id=26 --download-images
```

By default, the script uses `TEAM_ESPN_ID` from `.env` and exits with a non-zero status on failure.

### CSV import

The admin player import supports two modes:

1. CSV only
   Use this when the CSV contains image URLs or no image values.
2. CSV + ZIP
   Use this when the CSV was generated with `--local-images`.

In the CSV + ZIP flow, the backend stores the uploaded player images locally in `public/uploads/players/` and rewrites the imported image paths accordingly.

If the CSV includes an `id` column for every row, the import switches to sync mode:

- existing IDs are updated
- new IDs are created
- players missing from the uploaded ID set are deleted
- existing player ordering is preserved during updates

The current player model keeps a single `position` field plus metric storage in `weight_kg` and `height_cm`.

## Default admin login

- Username: `admin`
- Development password: `admin123`

For production, set your own `ADMIN_PASSWORD_HASH` and remove the plain fallback password.

## Notes

- The frontend can handle both absolute image URLs and local uploaded image paths.
- Simulator grouping is intentionally driven by the aliases in [`config/team.php`](config/team.php), not by the raw imported position alone.
- The Python importer includes an `--insecure` fallback for local SSL issues on macOS Python setups.
- `ordering` is only a manual display-order field for admin and simulator lists. The importer uses gaps like `10, 20, 30` so later reordering is easier.

## License and asset notice

The source code in this repository is licensed under the [MIT License](LICENSE).

Please note that this does not automatically apply to third-party names, logos, trademarks, player photos or other external content referenced or imported by the project. If you publish a public instance or redistribute assets, make sure you have the necessary rights for those materials.
