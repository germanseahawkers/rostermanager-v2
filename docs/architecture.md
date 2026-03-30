# Architecture

## Overview

`rostermanager-v2` is a lightweight PHP/MySQL application for NFL fan projects.

The current product is built around two connected surfaces:

- a public 90-to-53 roster cut simulator
- a protected admin backend for maintaining player data

The codebase intentionally avoids a full-stack framework. Instead, it uses a small custom application structure with explicit routing, simple controllers, PDO-based repositories, and server-rendered PHP views. That keeps deployment straightforward on classic shared hosting and Plesk-style environments while still preserving a clear separation of responsibilities.

## Design goals

The architecture is optimized for:

- simple deployment without Node.js or a build pipeline
- team-specific rebranding through configuration
- low operational overhead for fan club maintainers
- predictable imports and admin workflows
- shareable public output without requiring persistent user accounts

This project is meant to be adapted by other NFL fan communities, so branding, copy, position grouping, and roster limits are driven by configuration rather than hard-coded Seahawks-only assumptions.

## Runtime stack

- PHP 8.2+
- MySQL or MariaDB
- PDO for database access
- server-rendered HTML views
- session-based authentication for admin access
- small vanilla JavaScript for simulator interactivity

There is no ORM, no frontend framework, and no background worker layer.

## Request flow

The runtime request path is intentionally simple:

1. `public/index.php` bootstraps the application
2. the custom router resolves the route
3. a controller handles the request
4. repositories load or mutate data
5. helpers assemble view models and shared formatting
6. a PHP template renders the response

The core bootstrapping and HTTP primitives live in:

- [`public/index.php`](../public/index.php)
- [`src/Core/App.php`](../src/Core/App.php)
- [`src/Core/Router.php`](../src/Core/Router.php)
- [`src/Core/Request.php`](../src/Core/Request.php)
- [`src/Core/Response.php`](../src/Core/Response.php)
- [`src/Core/View.php`](../src/Core/View.php)
- [`src/Core/Database.php`](../src/Core/Database.php)
- [`src/Core/Session.php`](../src/Core/Session.php)
- [`src/Core/Env.php`](../src/Core/Env.php)

## Main application areas

### Public simulator

The public simulator is handled by [`src/Controllers/PublicRosterController.php`](../src/Controllers/PublicRosterController.php).

Its responsibilities are:

- resolve the requested locale
- parse roster selection from the query string
- parse personalization settings such as custom author name and share palette
- load players from the database
- build a grouped simulator payload
- render the simulator page, share page, or SVG share card

The simulator itself is mostly server-rendered, with JavaScript used for:

- toggling selected players
- updating counts in place
- keeping the selection in the URL
- updating share links
- applying local personalization preview state

Relevant files:

- [`src/Views/public/roster.php`](../src/Views/public/roster.php)
- [`src/Views/public/share.php`](../src/Views/public/share.php)
- [`public/assets/simulator.js`](../public/assets/simulator.js)
- [`public/assets/app.css`](../public/assets/app.css)

### Admin backend

The admin area is intentionally minimal and uses a single session-protected login.

It currently supports:

- player CRUD
- CSV roster import
- optional ZIP upload for local player images
- ID-based roster sync for offseason maintenance

Relevant files:

- [`src/Controllers/AuthController.php`](../src/Controllers/AuthController.php)
- [`src/Controllers/AdminPlayerController.php`](../src/Controllers/AdminPlayerController.php)
- [`src/Services/AuthService.php`](../src/Services/AuthService.php)
- [`src/Middleware/AuthMiddleware.php`](../src/Middleware/AuthMiddleware.php)
- [`src/Views/auth/login.php`](../src/Views/auth/login.php)
- [`src/Views/admin/players/index.php`](../src/Views/admin/players/index.php)

## Data model

The core persistent entity is `players`.

The current player model stores:

- `id`
- `name`
- `position`
- `experience`
- `weight_kg`
- `height_cm`
- `image`
- `ordering`

Important design decisions:

- `position` is the single canonical football position field
- `ordering` is only a manual display-order field
- imported player images may either stay as external URLs or be rewritten to local upload paths
- ESPN IDs can be used as stable primary keys for repeat imports

Schema files:

- [`database/schema.sql`](../database/schema.sql)
- [`database/migrate_players_to_metric_columns.sql`](../database/migrate_players_to_metric_columns.sql)

## Repository layer

Database access is deliberately explicit.

[`src/Repositories/PlayerRepository.php`](../src/Repositories/PlayerRepository.php) is responsible for:

- loading all players for public and admin screens
- creating players
- updating players
- deleting players
- handling sync-style imports where IDs determine update vs. insert behavior

This project keeps SQL close to the repository layer instead of spreading database queries across controllers or views.

## Configuration model

Configuration is split into two top-level concepts:

- application/runtime configuration
- team and club branding configuration

### Application configuration

[`config/app.php`](../config/app.php) reads:

- app name
- base path
- debug mode
- database credentials
- admin credentials
- club branding values

### Team configuration

[`config/team.php`](../config/team.php) contains:

- team name, city, nickname, slug
- logo path
- tagline
- theme colors
- roster limit
- position group mapping and aliases

### Club branding

The app also supports a second branding layer for the publishing fan club via:

- `CLUB_NAME`
- `CLUB_LOGO_PATH`
- `CLUB_TAGLINE`
- `CLUB_URL`

This allows the simulator to distinguish between:

- the NFL team being simulated
- the fan club or organization publishing the project

## Branding and theming

The visual system is driven by environment-configurable team colors:

- `TEAM_COLOR_PRIMARY`
- `TEAM_COLOR_SECONDARY`
- `TEAM_COLOR_SURFACE`
- `TEAM_COLOR_SURFACE_ALT`
- `TEAM_COLOR_TEXT`
- `TEAM_COLOR_INK`
- `TEAM_COLOR_MUTED`
- `TEAM_COLOR_LINE`

These values flow into:

- the base layout theme
- simulator styling
- share page styling
- derived share palettes for personalized roster cards

The personalized share palettes are no longer hard-coded to Seahawks-specific labels. Instead, the app derives three generic variants from the configured team palette:

- primary
- secondary
- neutral

That keeps setup simple for other teams and fan clubs.

## Simulator grouping model

Simulator grouping is configuration-driven.

Each player still has exactly one `position`, but the simulator groups positions through aliases defined in [`config/team.php`](../config/team.php).

Examples:

- `OT`, `OG`, `C`, `G`, and `T` roll up into `OL`
- `DE`, `DT`, and `NT` roll up into `DL`
- `K`, `PK`, `P`, and `LS` roll up into `ST`

This gives the application a stable public simulator structure while still allowing imports to preserve real football positions.

## Share architecture

The share system is stateless and query-string based.

The current share state is defined by:

- selected player IDs in `roster`
- locale in `lang`
- optional custom author name in `author`
- optional share palette in `scheme`

That state powers:

- the public simulator URL
- the share page
- the SVG share graphic

The share card renderer lives in [`src/Support/helpers.php`](../src/Support/helpers.php) and produces SVG server-side, which avoids needing a browser-based screenshot pipeline.

## Import architecture

Roster import is intentionally split into two stages:

1. fetch and normalize roster data locally
2. upload the result through the admin backend

### Local importer

[`scripts/import_roster.py`](../scripts/import_roster.py) reads from the ESPN roster API and can:

- fetch by team slug
- emit CSV for admin upload
- optionally download player headshots
- optionally generate a ZIP archive of those images
- write ESPN IDs into the CSV for sync-safe reimports

### Admin import

The admin import supports two main modes:

- plain CSV import
- CSV + ZIP import for local images

If every CSV row contains an `id`, the import switches into sync mode:

- existing IDs are updated
- missing IDs are deleted
- new IDs are created
- existing manual `ordering` values are preserved on updates

This makes offseason roster maintenance much easier than full wipe-and-recreate imports.

## Localization

The public UI supports:

- German
- English
- Spanish
- French
- Portuguese

Localization is handled through the shared translation helper in [`src/Support/helpers.php`](../src/Support/helpers.php). Locale selection happens via the `lang` query parameter, and unsupported locales fall back to English.

Position-group labels remain configuration-driven and are resolved through the simulator grouping map.

## Static and uploaded assets

There are two asset classes in the project:

### Bundled assets

- CSS
- JavaScript
- optional static logos under `public/`

### Uploaded assets

- player images stored under `public/uploads/players/`

The app can work with both remote image URLs and local upload paths, which keeps the deployment flexible.

## Current directory layout

- [`public/`](../public/) public web root, routing entry point, CSS and JavaScript
- [`src/Core/`](../src/Core/) framework-like primitives
- [`src/Controllers/`](../src/Controllers/) HTTP-facing application logic
- [`src/Repositories/`](../src/Repositories/) SQL-backed persistence
- [`src/Services/`](../src/Services/) focused business services such as auth
- [`src/Middleware/`](../src/Middleware/) route protection
- [`src/Views/`](../src/Views/) server-rendered templates
- [`src/Support/helpers.php`](../src/Support/helpers.php) shared formatting, simulator assembly, import helpers, share rendering
- [`config/`](../config/) app, team, and club configuration
- [`database/`](../database/) schema, migrations, and generated import files
- [`scripts/`](../scripts/) local data import tooling
- [`docs/`](../docs/) project documentation

## Intentional non-goals

The current architecture intentionally does not include:

- a full CMS
- user accounts or multi-user permissions
- persistent public roster submissions
- a background queue system
- a JavaScript SPA frontend
- automatic crawler-targeted OG image rendering beyond the existing share card endpoint

These may be added later, but the current architecture is deliberately biased toward simplicity and maintainability.

## Recommended direction for future growth

If the project grows further, the most natural extensions would be:

- saved named rosters in the database
- season-aware team and roster management
- multiple admin accounts
- richer social metadata and preview handling
- optional API endpoints for external clients

For now, the current architecture is intentionally small, explicit, and easy to rebrand, which fits the project goal well.
