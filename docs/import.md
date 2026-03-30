# Importing roster data

The project includes a local importer that pulls roster data from the ESPN NFL team roster API and writes a CSV for the admin backend.

It can also optionally download player headshots into a ZIP archive so the images can be uploaded once and served locally by this project.

If your installation still uses the older `abbr`, `weight` and `height` columns, run [`database/migrate_players_to_metric_columns.sql`](../database/migrate_players_to_metric_columns.sql) once before using the current import flow.

## Usage

Run the default Seahawks import:

```bash
python3 scripts/import_roster.py
```

Choose a different team via ESPN ID, slug or output file:

```bash
python3 scripts/import_roster.py \
  --team-slug seahawks \
  --output "database/imports/seahawks_active_roster.csv"
```

The slug can be either a short form such as `seahawks` or a full ESPN-style slug such as `seattle-seahawks`.

Use a custom endpoint template if needed:

```bash
python3 scripts/import_roster.py \
  --team-id 26 \
  --endpoint "https://site.api.espn.com/apis/site/v2/sports/football/nfl/teams/{team_id}/roster"
```

Generate a CSV plus a ZIP with all player images for local hosting:

```bash
python3 scripts/import_roster.py \
  --team-slug seahawks \
  --local-images
```

Optionally choose the ZIP output path:

```bash
python3 scripts/import_roster.py \
  --team-slug seahawks \
  --local-images \
  --zip-output "database/imports/seahawks_active_roster_images.zip"
```

If your local Python installation has SSL certificate issues on macOS, you can use:

```bash
python3 scripts/import_roster.py --insecure
```

That is only meant as a local fallback. The preferred long-term fix is to install or update your local Python CA certificates.

## Output format

The script writes a CSV with these headers:

```text
id,name,position,experience,weight_kg,height_cm,image,ordering
```

Notes:

- `id` is optional, but recommended for recurring offseason sync imports
- The real ESPN position abbreviation is preserved in `position`
- Simulator grouping still happens through the aliases in [`config/team.php`](../config/team.php)
- Height is normalized to centimeters
- Weight is normalized to kilograms
- By default, `image` uses the ESPN `headshot.href` value when available
- With `--local-images`, `image` contains the local filename that is also written into the ZIP archive
- The ESPN importer writes the ESPN player ID into the CSV `id` column

## Admin import modes

The admin backend supports two import paths:

1. Direct ESPN import
2. CSV-based import

## Direct ESPN import

The admin UI includes a built-in ESPN importer.

You only need:

- an ESPN team ID, such as `26` for the Seahawks
- optional local image download if you want to store player headshots on your own server

The backend will then:

- request the ESPN roster API directly
- normalize the response into the internal player structure
- use ESPN player IDs as stable sync IDs
- update existing players
- insert new players
- delete players that are no longer present in the imported ESPN roster
- preserve existing manual ordering for players that already exist in the database

If local image download is enabled:

- headshots are downloaded server-side
- files are stored under `public/uploads/players/`
- imported player image paths are rewritten to local paths automatically

This is the recommended default workflow for simple offseason maintenance.

## Automated ESPN import via cron

The same import flow is also available as a CLI script:

```bash
php scripts/import_espn.php
```

By default, the script uses `TEAM_ESPN_ID` from `.env`.

Optional flags:

```bash
php scripts/import_espn.php --team-id=26 --download-images
```

This makes it easy to run a daily offseason sync via cron.

## CSV import

The admin backend supports two import modes:

1. CSV only
   Use this when the `image` column contains absolute URLs or is empty.
2. CSV plus ZIP
   Use this when the CSV was generated with `--local-images`.

Import behavior depends on the CSV:

- Without `id`, each row is inserted as a new player record
- With `id` in every row, the import becomes a sync
- Existing IDs are updated
- New IDs are inserted
- Players not present in the uploaded ID list are deleted
- Ordering is not overwritten for existing players during ID-based updates

In CSV plus ZIP mode:

- the ZIP is uploaded together with the CSV
- the backend stores the images in `public/uploads/players/`
- the imported player rows are rewritten to local image paths

If a CSV image reference cannot be matched to a file in the ZIP archive, the import fails with a clear error instead of partially importing the roster.

## Typical workflow

1. Run the script locally
2. If you used `--local-images`, keep the generated ZIP next to the CSV
3. Check the generated files in [`database/imports/`](../database/imports/)
4. Open the admin player import
5. Upload the CSV
6. If applicable, upload the matching images ZIP as well
