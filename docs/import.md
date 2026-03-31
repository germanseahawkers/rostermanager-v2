# Importing roster data

`RosterManager v2` supports three practical roster import workflows:

1. direct ESPN import in the admin backend
2. automated ESPN sync from the command line
3. optional local CSV generation through Python

For most deployments, the direct ESPN import is the recommended default.

## Data model

The current import target expects these player fields:

```text
id,name,position,experience,weight_kg,height_cm,image,ordering
```

Notes:

- `id` is used for stable sync imports and should ideally be the ESPN player ID
- `position` keeps the real football position abbreviation from the source
- simulator grouping is derived separately from aliases in [`config/team.php`](../config/team.php)
- `weight_kg` and `height_cm` are stored in metric units
- `image` can hold either an external URL or a local upload path
- `ordering` is a manual display-order value

## Recommended workflow: admin ESPN import

The admin backend includes a direct ESPN importer.

You only need:

- an ESPN team ID, for example `26` for the Seahawks
- optional local image download if you want to host headshots yourself

When triggered, PHP will:

- fetch the ESPN roster endpoint
- normalize the JSON payload
- sync by ESPN player ID
- update existing players
- create new players
- delete players no longer present in the imported roster
- preserve manual ordering for players that already exist

If local image download is enabled:

- player headshots are downloaded server-side
- files are stored under `public/uploads/players/`
- existing local images are reused on later syncs
- unreferenced images are removed automatically after a successful import

This is the best option for regular offseason maintenance.

## Automated ESPN import via CLI

The same sync flow is available from the command line:

```bash
php scripts/import_espn.php
```

By default, the script uses `TEAM_ESPN_ID` from `.env`.

Optional flags:

```bash
php scripts/import_espn.php --team-id=26 --download-images
```

This is intended for cron-based automation.

Example cron entry:

```cron
0 3 * * * cd "/path/to/rostermanager-v2" && /usr/bin/php scripts/import_espn.php --download-images >> /tmp/rostermanager-espn-import.log 2>&1
```

Adjust the PHP path to your environment.

## Optional workflow: local CSV generation

The Python helper remains available for maintainers who want to prepare roster data locally before importing it into the admin backend.

Basic usage:

```bash
python3 scripts/import_roster.py --team-slug seahawks
```

Generate local images as well:

```bash
python3 scripts/import_roster.py --team-slug seahawks --local-images
```

Optional examples:

```bash
python3 scripts/import_roster.py --team-id 26
python3 scripts/import_roster.py --team-slug seattle-seahawks
python3 scripts/import_roster.py --team-slug seahawks --output "database/imports/seahawks.csv"
python3 scripts/import_roster.py --team-slug seahawks --local-images --zip-output "database/imports/seahawks-images.zip"
```

If your local Python setup has SSL certificate issues on macOS, you can temporarily use:

```bash
python3 scripts/import_roster.py --insecure
```

## CSV import behavior

The admin CSV import supports two modes:

1. CSV only
2. CSV plus ZIP

### CSV only

Use this when:

- `image` contains external URLs
- or no local images are needed

### CSV plus ZIP

Use this when the CSV was generated with `--local-images`.

In that case:

- the ZIP is uploaded alongside the CSV
- image files are stored in `public/uploads/players/`
- imported rows are rewritten to local image paths

If a referenced image file is missing from the ZIP, the import fails with a clear error instead of partially importing the roster.

## Sync behavior with `id`

Import behavior changes depending on whether every CSV row contains an `id`.

### Without `id`

- each row is inserted as a new player

### With `id` in every row

- existing IDs are updated
- new IDs are inserted
- players missing from the uploaded ID set are deleted
- ordering is preserved for existing players

Mixed CSV files with IDs in only some rows are rejected.

## Local image strategy

When local image mode is used, the app avoids filling the server with duplicates:

- existing local player images are reused
- new images are downloaded only when needed
- files no longer referenced by any player are removed after a successful sync

This applies to both the admin ESPN import and the CLI ESPN import.

## Typical maintenance flows

### Manual offseason update

1. Open the admin backend
2. Use direct ESPN import
3. Optionally enable local image download
4. Review the simulator

### Automated daily sync

1. Set `TEAM_ESPN_ID` in `.env`
2. Configure `scripts/import_espn.php` in cron
3. Optionally enable `--download-images`
4. Check the import log if something fails

### Local editorial workflow

1. Run `scripts/import_roster.py`
2. Review the generated CSV and optional ZIP
3. Import both through the admin backend

## Related files

- [`scripts/import_espn.php`](../scripts/import_espn.php)
- [`scripts/import_roster.py`](../scripts/import_roster.py)
- [`src/Controllers/AdminPlayerController.php`](../src/Controllers/AdminPlayerController.php)
- [`src/Repositories/PlayerRepository.php`](../src/Repositories/PlayerRepository.php)
- [`src/Support/helpers.php`](../src/Support/helpers.php)
