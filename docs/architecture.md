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
- CSV-Import, CRUD und öffentliche Darstellung lassen sich damit sehr robust umsetzen

## MVP-Zuschnitt

- Öffentlich:
  - 90-to-53 roster cut simulator
  - Positionsgruppen als konfigurierbare Tabs
  - Live-Zähler und Review-Bereich
  - Sprachumschaltung Deutsch/Englisch
  - Share-URL pro Ergebnis
  - Serverseitig gerenderte SVG-Share-Grafik
- Admin:
  - Login mit einem einfachen Admin-Zugang
  - Spieler anlegen, bearbeiten, löschen
  - CSV-Import für Spieler
- Vorbereitet, aber noch bewusst nicht umgesetzt:
  - Rollenmodell mit mehreren Benutzern
  - Persistente Ergebnis-Speicherung
  - Team-/Saisonverwaltung im Backend
  - Automatische OG/Social-Meta-Ausgabe für Crawler

## Open-Source-Fokus

- Team-Name, Farben, Tagline und Logo-Pfad liegen zentral in `config/team.php`
- Alle Teamwerte können zusätzlich per `.env` überschrieben werden
- Positionsgruppen und Aliase sind zentral konfigurierbar
- Damit kann dieselbe Codebasis von anderen NFL-Fanklubs mit wenig Aufwand übernommen werden

## Ordnerstruktur

- `public/`: Front Controller, Assets, Webserver-Einstieg
- `src/Controllers/`: HTTP-nahe Anwendungslogik
- `src/Repositories/`: Datenzugriff auf SQL-Ebene
- `src/Services/`: fachliche Services wie Auth
- `src/Core/`: Router, Request, Response, View, Session, DB
- `src/Views/`: einfache serverseitige Templates
- `config/`: Konfiguration über Umgebungsvariablen
- `database/`: SQL-Schema und Seed-Daten
- `storage/`: Platzhalter für spätere Uploads/Exports
