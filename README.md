# rostermanager-v2

Lean greenfield rebuild of the legacy roster manager with a clear MVP focus.

## Chosen direction

Because the target environment is Plesk-style hosting with PHP and SQL, this project now uses:

- PHP 8.2+
- MySQL/MariaDB via PDO
- Server-rendered views
- Session-based admin authentication
- A very small custom application structure instead of a heavy framework

This keeps deployment simple and still gives us a clean separation between routing, controllers, business logic, and data access.

## MVP scope

- Public roster page
- Group players by position
- German/English language toggle
- Admin login
- Player CRUD
- CSV import for players

## Current structure

- [public](/Users/simonkell/kDrive/German Sea Hawkers/rostermanager-v2/public)
- [src](/Users/simonkell/kDrive/German Sea Hawkers/rostermanager-v2/src)
- [config](/Users/simonkell/kDrive/German Sea Hawkers/rostermanager-v2/config)
- [database](/Users/simonkell/kDrive/German Sea Hawkers/rostermanager-v2/database)
- [docs/architecture.md](/Users/simonkell/kDrive/German Sea Hawkers/rostermanager-v2/docs/architecture.md)

## Setup

1. Point the webroot to [public](/Users/simonkell/kDrive/German Sea Hawkers/rostermanager-v2/public).
2. Create a MySQL database and import [database/schema.sql](/Users/simonkell/kDrive/German Sea Hawkers/rostermanager-v2/database/schema.sql).
3. Configure environment variables based on [.env.example](/Users/simonkell/kDrive/German Sea Hawkers/rostermanager-v2/.env.example).
4. Ensure Apache `mod_rewrite` is enabled so [public/.htaccess](/Users/simonkell/kDrive/German Sea Hawkers/rostermanager-v2/public/.htaccess) can route requests through `index.php`.

## Default admin login

- Username: `admin`
- Development password: `admin123`

For deployment, set your own `ADMIN_PASSWORD_HASH` and remove the plain fallback password.
