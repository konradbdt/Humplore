# Humplore Architektur

Stand: 24. Juli 2026

## Laufzeit

- PHP 8 mit serverseitig gerenderten HTML-Seiten
- SQLite als aktuelle Datenbank
- JavaScript direkt in den Seiten für Fetch- und UI-Interaktionen
- keine erkennbare Framework- oder Paketmanager-Abhängigkeit für den Kernbetrieb

## Aktive Arbeitskopie

`Webseite - Redesign/` ist die zuletzt bearbeitete Arbeitskopie. Der nahezu
identische Baum `Webseite - Codex/` bleibt bis zur Git-Wiederherstellung als
Vergleichsbasis erhalten und wird nicht parallel weiterentwickelt.

## Anwendungsstruktur

| Pfad | Verantwortung |
|---|---|
| `Webseite - Redesign/*.php` | Öffentliche Routen und Seitencontroller |
| `Webseite - Redesign/app/bootstrap.php` | Session, Datenbank und gemeinsame Support-Funktionen |
| `Webseite - Redesign/app/support/` | Wiederverwendbare Auth-, Content-, Such-, Profil-, Filter- und Reportinglogik |
| `Webseite - Redesign/app/views/partials/` | Wiederverwendbare Navigation, Postkarten, Profil- und Reportingoberflächen |
| `Webseite - Redesign/config/` | Laufzeitkonfiguration und Datenbankverbindung |
| `Webseite - Redesign/data/` | Lokale SQLite-Laufzeitdaten |
| `Webseite - Redesign/css/` | Globale Styles |
| `Webseite - Redesign/inc/` | Rückwärtskompatible Include-Einstiegspunkte |
| `Webseite - Redesign/legacy/` | Nicht produktive Backups und Experimente |
| `Webseite - Redesign/tmp/` | Testausgaben, Browserprofile und Logs |

Öffentliche URLs wie `platform.php`, `search.php`, `profile.php` und
`posten.php` bleiben stabil. Neue gemeinsame Logik gehört bevorzugt nach
`app/support/`.

## Datenhaltung

- Schemaänderungen werden additiv ausgeführt.
- `humplore_ensure_database_schema()` stellt derzeit unter anderem
  `Posts.source_question_id`, `SavedPosts` und `Reports` defensiv sicher.
- Datenbankzugriffe verwenden vorbereitete Statements.
- Vor einem MySQL-Wechsel sind Schema-Kompatibilität, Migration, Backups und
  Hosting verbindlich zu planen.

## Sicherheitsgrundlagen

- Zustandsändernde Fetch-Endpunkte sollen POST, Login und CSRF prüfen.
- Ausgaben werden über vorhandene Escaping-Helfer behandelt.
- Reporting und Speichern verwenden eigene JSON-Handler.
- Betreiberzuordnungen anonymer Inhalte bleiben intern erhalten.

## Prüfung

Die verbindliche Vorgehensweise steht in [`teststrategie.md`](teststrategie.md)
und in der `tasks.md` des jeweiligen OpenSpec-Changes.
