# Humplore Architektur

Stand: 28. Juli 2026

## Laufzeit

- PHP 8 mit serverseitig gerenderten HTML-Seiten
- SQLite als aktuelle Datenbank
- JavaScript direkt in den Seiten für Fetch- und UI-Interaktionen
- keine erkennbare Framework- oder Paketmanager-Abhängigkeit für den Kernbetrieb

## Aktive Arbeitskopie

`Webseite - Codex/` ist der einzige maßgebliche Codebaum. `Webseite - Redesign/`
ist verworfen und weder Test- noch Implementierungsgrundlage.

## Anwendungsstruktur

| Pfad | Verantwortung |
|---|---|
| `Webseite - Codex/*.php` | Öffentliche Routen und Seitencontroller |
| `Webseite - Codex/app/bootstrap.php` | Session, Datenbank und gemeinsame Support-Funktionen |
| `Webseite - Codex/app/support/` | Wiederverwendbare Auth-, Content-, Such-, Profil-, Filter- und Reportinglogik |
| `Webseite - Codex/app/views/partials/` | Wiederverwendbare Navigation, Postkarten, Profil- und Reportingoberflächen |
| `Webseite - Codex/config/` | Laufzeitkonfiguration und Datenbankverbindung |
| `Webseite - Codex/data/` | Lokale SQLite-Laufzeitdaten |
| `Webseite - Codex/css/` | Globale Styles |
| `Webseite - Codex/inc/` | Rückwärtskompatible Include-Einstiegspunkte |
| `Webseite - Codex/legacy/` | Nicht produktive Backups und Experimente |
| `Webseite - Codex/tmp/` | Testausgaben, Browserprofile und Logs |

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

## Medienstand

- Die aktive Erstellseite `posten.php` akzeptiert und speichert ausschließlich
  Bilder in `Posts.media_image`.
- Video-Uploads sind im aktiven Posting-Workflow nicht implementiert.
- Die Feed- und Suchkarten deklarieren gespeicherte Bilder derzeit pauschal als
  JPEG. PNG-, WebP- und GIF-Dateien können deshalb trotz erfolgreichem Upload
  falsch oder gar nicht angezeigt werden.
- Die alte Route `create_post.php` mit `process_post.php` ist nicht aus der
  Anwendung verlinkt und erwartet zusätzlich einen nicht vorhandenen
  `uploads/`-Ordner. Sie ist keine funktionierende Medienbasis.

## Sicherheitsgrundlagen

- Zustandsändernde Fetch-Endpunkte sollen POST, Login und CSRF prüfen.
- Ausgaben werden über vorhandene Escaping-Helfer behandelt.
- Reporting und Speichern verwenden eigene JSON-Handler.
- Betreiberzuordnungen anonymer Inhalte bleiben intern erhalten.

## Prüfung

Die verbindliche Vorgehensweise steht in [`teststrategie.md`](teststrategie.md)
und in der `tasks.md` des jeweiligen OpenSpec-Changes.
