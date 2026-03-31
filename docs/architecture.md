# Architecture

## Overview

`RosterManager v2` is a lightweight PHP/MySQL application for NFL fan communities.

The current product combines four main areas:

- a public 90-to-53 roster cut simulator
- a shareable personalized roster page
- a protected admin backend for player maintenance
- an ESPN-based import pipeline for ongoing roster sync

The codebase intentionally avoids a full-stack framework. It uses a small custom application structure with explicit routing, PDO repositories, shared helper functions, server-rendered views, and a small amount of vanilla JavaScript.

## Goals

The architecture is optimized for:

- simple deployment on classic PHP hosting
- easy rebranding for different fan clubs and NFL teams
- low operational overhead
- predictable import behavior
- link-based sharing without user accounts
- minimal runtime dependencies

## Runtime stack

- PHP 8.2+
- MySQL or MariaDB
- PDO
- session-based authentication for admin access
- server-rendered HTML templates
- vanilla JavaScript for simulator interactions

There is no ORM, no SPA frontend, and no background worker layer.

## Request flow

The runtime path is deliberately simple:

1. [`public/index.php`](../public/index.php) boots the app
2. [`src/Core/Router.php`](../src/Core/Router.php) resolves the route
3. a controller handles the request
4. repositories and helpers load or transform data
5. a PHP view renders the response

Core infrastructure lives in:

- [`src/Core/App.php`](../src/Core/App.php)
- [`src/Core/Request.php`](../src/Core/Request.php)
- [`src/Core/Response.php`](../src/Core/Response.php)
- [`src/Core/View.php`](../src/Core/View.php)
- [`src/Core/Database.php`](../src/Core/Database.php)
- [`src/Core/Session.php`](../src/Core/Session.php)
- [`src/Core/Env.php`](../src/Core/Env.php)

## Public application surface

The public surface is handled by [`src/Controllers/PublicRosterController.php`](../src/Controllers/PublicRosterController.php).

It is responsible for:

- locale resolution
- loading saved share tokens
- restoring simulator state from a saved share
- resolving personalized palette variants
- generating the simulator page
- generating the share page
- creating share tokens
- rendering the legacy SVG share asset

### Simulator page

The simulator page is rendered in [`src/Views/public/roster.php`](../src/Views/public/roster.php) and enhanced by [`public/assets/simulator.js`](../public/assets/simulator.js).

JavaScript is used for:

- switching active position groups
- selecting and deselecting players
- updating counts in place
- updating personalized preview state
- lazily creating short share links only when a share action is triggered

The browser URL no longer mutates live during selection. A persistent link is created only when the user explicitly shares.

### Share page

The share page is rendered in [`src/Views/public/share.php`](../src/Views/public/share.php).

It reuses the saved roster state and shows:

- personalized headline
- club-branded hero area
- quick share actions
- grouped roster sections with expandable player lists

The share page also provides social metadata through [`src/Views/layouts/app.php`](../src/Views/layouts/app.php), including:

- `canonical`
- Open Graph title, description and image
- Twitter card metadata

Social previews currently use the club logo from `CLUB_LOGO_PATH` as the primary preview image.

## Share architecture

Sharing is database-backed rather than purely query-string based.

The relevant persistence lives in [`src/Repositories/ShareRepository.php`](../src/Repositories/ShareRepository.php).

A share stores:

- roster player IDs
- locale
- author name
- selected share palette
- short token

The flow is:

1. simulator posts the current state to `POST /share/create`
2. backend creates or reuses a short token
3. share URL becomes `/?s=TOKEN` or `/share?s=TOKEN`
4. simulator and share page can both restore state from the token

This keeps URLs short and stable while preserving personalization.

## Admin backend

The admin side is handled by:

- [`src/Controllers/AuthController.php`](../src/Controllers/AuthController.php)
- [`src/Controllers/AdminPlayerController.php`](../src/Controllers/AdminPlayerController.php)
- [`src/Middleware/AuthMiddleware.php`](../src/Middleware/AuthMiddleware.php)
- [`src/Views/auth/login.php`](../src/Views/auth/login.php)
- [`src/Views/admin/players/index.php`](../src/Views/admin/players/index.php)

Current admin capabilities:

- login/logout
- player CRUD
- CSV import
- CSV + ZIP import for local player photos
- direct ESPN import

## Data model

The main persistent entity is `players`.

Important player fields:

- `id`
- `name`
- `position`
- `experience`
- `weight_kg`
- `height_cm`
- `image`
- `ordering`

Design decisions:

- `position` is the only canonical football position field
- simulator grouping is derived from configured aliases, not stored separately
- `ordering` is a manual display-order field, not a football rule
- `image` can be either an external URL or a local upload path
- ESPN player IDs can serve as stable sync IDs

The short-link model is stored separately in `shares`.

Schema and migrations live in:

- [`database/schema.sql`](../database/schema.sql)
- [`database/migrate_players_to_metric_columns.sql`](../database/migrate_players_to_metric_columns.sql)
- [`database/migrate_add_shares_table.sql`](../database/migrate_add_shares_table.sql)

## Repository layer

Data access is explicit and close to SQL.

Main repositories:

- [`src/Repositories/PlayerRepository.php`](../src/Repositories/PlayerRepository.php)
- [`src/Repositories/ShareRepository.php`](../src/Repositories/ShareRepository.php)

This keeps database logic out of controllers and avoids hidden ORM behavior.

## Configuration model

Configuration is split into two branding layers plus runtime settings.

### App/runtime configuration

[`config/app.php`](../config/app.php) reads:

- app name
- base path
- debug mode
- database settings
- admin credentials
- club branding

### Team configuration

[`config/team.php`](../config/team.php) contains:

- team identity
- logo path
- ESPN team ID
- roster limit
- position group aliases
- theme colors

### Club configuration

The fan-club publisher is configured separately through:

- `CLUB_NAME`
- `CLUB_LOGO_PATH`
- `CLUB_URL`
- optional club tagline/copy inputs

This makes it possible to run a Seahawks-themed simulator that is clearly published by a separate club organization.

## Theming and palettes

The main UI theme comes from `TEAM_COLOR_*` values in `.env`.

The personalized preview/share palettes are derived from those base colors rather than requiring a second full palette configuration. The app currently exposes three variants:

- primary
- secondary
- neutral

These variants are used in the simulator preview and on the share page.

## Localization

Translations are centralized in [`src/Support/helpers.php`](../src/Support/helpers.php).

Locale resolution follows this order:

1. explicit `lang` in the URL
2. saved share locale
3. browser `Accept-Language`
4. fallback to `en`

Supported locales currently include:

- German
- English
- Spanish
- French
- Portuguese

## Simulator grouping model

Players are grouped for the simulator through aliases defined in [`config/team.php`](../config/team.php).

Examples:

- `OT`, `OG`, `C`, `G`, `T` -> `OL`
- `DE`, `DT`, `NT` -> `DL`
- `K`, `PK`, `P`, `LS` -> `ST`

This lets imports preserve real source positions while the public simulator stays readable.

## Import architecture

There are three import paths:

### Admin ESPN import

The primary workflow. The admin enters an ESPN team ID and PHP:

- fetches the roster from ESPN
- normalizes the payload
- syncs by ESPN player ID
- updates existing players
- inserts new players
- removes missing players
- preserves manual ordering

Optional image download stores local files in `public/uploads/players/`.

### CLI ESPN import

[`scripts/import_espn.php`](../scripts/import_espn.php) runs the same sync flow from the command line and is intended for cron-based roster maintenance.

### Local CSV preparation

[`scripts/import_roster.py`](../scripts/import_roster.py) can still be used when maintainers want a local CSV and optional image ZIP before import.

## Image handling

Player images can be handled in two ways:

- leave external URLs untouched
- download and store local copies

For local image mode, imports:

- store files in `public/uploads/players/`
- reuse existing local images on later syncs
- remove files no longer referenced after a successful sync

This prevents duplicate downloads while keeping the upload directory clean.

## Current limitations

- no automated test suite yet
- no background queue layer
- no PNG-based dynamic social card generator
- social previews currently rely on the club logo instead of a per-roster rendered image

## Extension points

The current structure leaves room for:

- additional leagues or teams
- richer admin analytics
- a real social-card image pipeline
- bulk edit tools
- stronger validation or automated tests
