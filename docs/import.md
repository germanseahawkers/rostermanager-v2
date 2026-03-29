# Importing roster data

The project includes a small local importer that pulls roster data from the ESPN NFL team roster API and turns it into a CSV for the admin UI.

## Usage

Run the default Seahawks import:

```bash
python3 scripts/import_roster.py
```

Choose a different team or output file:

```bash
python3 scripts/import_roster.py \
  --team-id 26 \
  --output "database/imports/seahawks_active_roster.csv"
```

Use a custom endpoint template if needed:

```bash
python3 scripts/import_roster.py \
  --team-id 26 \
  --endpoint "https://site.api.espn.com/apis/site/v2/sports/football/nfl/teams/{team_id}/roster"
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
- `image` uses the ESPN `headshot.href` value when available

## Typical workflow

1. Run the script locally
2. Check the generated CSV in `database/imports/`
3. Upload the CSV in the admin backend
