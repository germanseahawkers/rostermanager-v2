# Importing roster data

The project includes a small local scraper that turns an NFL team roster page into a CSV that can be uploaded through the admin UI.

## Usage

Run the default Seahawks import:

```bash
python3 scripts/import_roster.py
```

Choose a different source URL or output file:

```bash
python3 scripts/import_roster.py \
  --url "https://www.seahawks.com/team/players-roster/" \
  --output "database/imports/seahawks_active_roster.csv"
```

If your local Python installation has SSL certificate issues on macOS, you can use:

```bash
python3 scripts/import_roster.py --insecure
```

That is only meant as a local fallback. The preferred long-term fix is to install/update your local Python CA certificates.

## Output format

The script writes a CSV with these headers:

```text
name,position,abbr,experience,weight_kg,height_cm,image,ordering
```

Notes:

- The real position is preserved in `position`
- Simulator grouping still happens through the mapping in `config/team.php`
- Height is normalized to centimeters
- Weight is normalized to kilograms
- `image` tries to pull the official player image URL from the player profile page

## Typical workflow

1. Run the script locally
2. Check the generated CSV in `database/imports/`
3. Upload the CSV in the admin backend
