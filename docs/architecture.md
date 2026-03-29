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
  - Rosterliste nach Position gruppiert
  - Filter nach Position
  - Sprachumschaltung Deutsch/Englisch
- Admin:
  - Login mit einem einfachen Admin-Zugang
  - Spieler anlegen, bearbeiten, löschen
  - CSV-Import für Spieler
- Vorbereitet, aber noch bewusst nicht umgesetzt:
  - Rollenmodell mit mehreren Benutzern
  - Upload-Management für Bilder
  - Export/Veröffentlichungsworkflow

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
