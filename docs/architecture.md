# Zielarchitektur

## Empfohlener Stack

- PHP 8.2+ ohne schweres Backend-Framework
- MySQL oder MariaDB via PDO
- Serverseitig gerenderte Views für Public und Admin
- Session-basierte Admin-Authentifizierung
- `password_hash()` und `password_verify()` statt veralteter Hashes
- Vanilla CSS und nur minimales Browser-JavaScript

## Warum diese Richtung

- Passt direkt zu Plesk-Hosting ohne Node-Laufzeit
- Einfache Deployment-Struktur mit `public/` als Webroot
- Fachliche Trennung bleibt sauber, ohne unnötige Framework-Komplexität
- CSV-Import, lokaler Bild-Upload, CRUD und öffentliche Darstellung lassen sich damit robust umsetzen

## MVP-Zuschnitt

Öffentlich:

- 90-to-53 roster cut simulator
- Positionsgruppen als konfigurierbare Tabs
- Live-Zähler und Review-Bereich
- Sprachumschaltung Deutsch/Englisch
- Share-URL pro Ergebnis
- Serverseitig gerenderte SVG-Share-Grafik

Admin:

- Login mit einem einfachen Admin-Zugang
- Spieler anlegen, bearbeiten, löschen
- CSV-Import für Spieler
- Optionaler ZIP-Import für lokale Spielerbilder

Vorbereitet, aber noch bewusst nicht umgesetzt:

- Rollenmodell mit mehreren Benutzern
- Persistente Ergebnis-Speicherung
- Team- oder Saisonverwaltung im Backend
- Automatische OG- oder Social-Meta-Ausgabe für Crawler

## Open-Source-Fokus

- Team-Name, Farben, Tagline und Logo-Pfad liegen zentral in [`config/team.php`](../config/team.php)
- Alle Teamwerte können zusätzlich per [`.env.example`](../.env.example) überschrieben werden
- Positionsgruppen und Aliase sind zentral konfigurierbar
- Damit kann dieselbe Codebasis von anderen NFL-Fanklubs mit wenig Aufwand übernommen werden

## Ordnerstruktur

- [`public/`](../public/) Front Controller, Assets, Webserver-Einstieg
- [`src/Controllers/`](../src/Controllers/) HTTP-nahe Anwendungslogik
- [`src/Repositories/`](../src/Repositories/) Datenzugriff auf SQL-Ebene
- [`src/Services/`](../src/Services/) fachliche Services wie Auth
- [`src/Core/`](../src/Core/) Router, Request, Response, View, Session und DB
- [`src/Views/`](../src/Views/) einfache serverseitige Templates
- [`config/`](../config/) Konfiguration über Umgebungsvariablen
- [`database/`](../database/) SQL-Schema und lokale Importdateien
- [`storage/`](../storage/) Platzhalter für spätere Uploads oder Exports

## Import- und Bildpfad

Der aktuelle Roster-Import ist bewusst zweistufig:

1. Lokal per [`scripts/import_roster.py`](../scripts/import_roster.py)
2. Danach Upload im Admin

Dabei gibt es zwei Varianten:

- CSV mit externen Bild-URLs
- CSV plus ZIP mit lokalen Bilddateien

Die lokale Bildvariante reduziert externe CDN-Abhängigkeiten im Frontend und speichert die Assets dauerhaft unter `public/uploads/players/`.
