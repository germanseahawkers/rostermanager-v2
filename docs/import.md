# Importing roster data

The project includes a small local importer that pulls roster data from the ESPN NFL team roster API and turns it into a CSV for the admin UI. It can also optionally download all player headshots into a ZIP for local server uploads.

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
name,position,abbr,experience,weight_kg,height_cm,image,ordering
```

Notes:

- The real ESPN position abbreviation is preserved in `position`
- Simulator grouping still happens through the mapping in `config/team.php`
- Height is normalized to centimeters
- Weight is normalized to kilograms
- By default, `image` uses the ESPN `headshot.href` value when available
- With `--local-images`, `image` contains the local filename that is also written into the ZIP archive

## Typical workflow

1. Run the script locally
2. If you used `--local-images`, keep the generated ZIP next to the CSV
3. Check the generated files in `database/imports/`
4. Upload the CSV in the admin backend
5. Optionally upload the images ZIP together with the CSV so the server stores the player pictures locally
