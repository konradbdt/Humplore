# Humplore Architektur

Stand: 31. August 2026

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
| `Webseite - Codex/*.php` | Direkt erreichbare Routen und Seitencontroller mit expliziter Zugangsklasse |
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
- Feed-, Such- und Profilkarten laden Beitragsbilder über `media.php` mit
  serverseitig erkanntem MIME-Type. Neue Beitragsbilder sind auf JPEG, PNG und
  WebP begrenzt.
- Die alte Route `create_post.php` mit `process_post.php` ist nicht aus der
  Anwendung verlinkt und erwartet zusätzlich einen nicht vorhandenen
  `uploads/`-Ordner. Beide Einstiege sind deaktiviert (HTTP 404) und keine
  funktionierende Medienbasis.

## Sicherheitsgrundlagen

- Explore, Suche, Profile, Beiträge und Interaktionen sind geschützte
  Anwendungsbereiche und setzen eine Anmeldung voraus. Nur Einstieg,
  Registrierung, Login sowie erforderliche Informations-, Kontakt- und
  Rechtsseiten bleiben öffentlich erreichbar.
- Zustandsändernde Fetch-Endpunkte sollen POST, Login und CSRF prüfen.
- Ausgaben werden über vorhandene Escaping-Helfer behandelt.
- Reporting und Speichern verwenden eigene JSON-Handler.
- Betreiberzuordnungen anonymer Inhalte bleiben intern erhalten.

### Routenzugriffsschutz

Die verbindliche Zugangsregel aus `produktumfang.md` wird vor dem Laden
geschützter Daten angewendet. Die Auth-Helfer liegen gemeinsam in
`app/support/auth.php`; eine fehlende Anmeldung führt je nach Antworttyp zum
Login oder zu HTTP 401, nicht zu einer HTML-Loginseite in einer Fetch-Antwort.

| Klasse | Routen | Verhalten |
|---|---|---|
| Öffentliche Einstiege | `index.html`, `login.php`, `register.php`, `agbs.html`, `impressum.html`, `kontakt.html`, `ueber-uns.html` | Ohne Humplore-Anmeldung erreichbar. |
| Öffentliche Auth-Handler | `process_login.php`, `process_register.php` | POST bleibt ohne Anmeldung möglich; andere Methoden liefern 405 mit `Allow: POST`. |
| Geschützte Browserseiten | `platform.php`, `search.php`, `profile.php`, `posten.php`, `news.php`, `fragen.php` | `humplore_require_login()` führt Gäste mit geprüftem lokalem Rückkehrziel zum Login. `posten.php` verlangt zusätzlich Creator-Status. |
| Logout | `logout.php` | Verlangt eine Anmeldung; Gäste gehen ohne Rückkehrziel zum Login, damit kein Login-/Logout-Kreis entsteht. |
| Geschützte JSON-Handler | `like_handler.php`, `report_handler.php`, `save_post_handler.php` | `humplore_require_json_login()` liefert für nicht angemeldete POST-Anfragen JSON mit HTTP 401 und ohne Redirect. Bestehende Validierung und Antwortformate bleiben erhalten. |
| Geschützte Medien | `media.php` | `humplore_require_resource_login()` liefert Gästen 401 ohne Redirect. Angemeldet sind GET/HEAD erlaubt; Bilder werden mit `private, no-store` ausgeliefert. |
| Nicht produktive Root-Einstiege | `create_post.php`, `process_post.php`, `get_profile_image.php`, `save_profile.php`, `update_profile.php`, `info.php`, `egal.php`, `ionos_check.php` | Gemeinsame Abweisung mit 404 vor Datenbankzugriff, Upload oder Diagnoseausgabe, auch für angemeldete Nutzer. |

Die deaktivierten Schreib-/Medienrouten sind nicht aus der aktiven Anwendung
verlinkt; ihre Aufgaben werden durch `posten.php`, die Profilaktionen in
`profile.php` und `media.php` erfüllt. Die unverbundene Fragen-Kompatibilitätsseite
bleibt dagegen loginpflichtig nutzbar. Dies ändert keine Produktentscheidung.
URL-gesteuerte Diagnoseausgaben in `profile.php` und `media.php` sind entfernt;
`debug`-Parameter schalten keine technischen Informationen mehr frei.

Interne Dateien sind keine Anwendungsrouten: `.htaccess` mit `Require all denied`
sperrt HTTP-Zugriffe auf `app/`, `config/`, `data/`, `inc/`, `legacy/` und `tmp/`
einschließlich ihrer PHP-Dateien, Backups und Testartefakte. Die Root-`.htaccess`
sperrt zusätzlich Dotfiles sowie DB-, SQL-, INI-, Log-, Markdown- und
Passwortdateien. PHP-Includes bleiben davon unberührt. Die frühere
globale HTTP-Basic-Auth-Konfiguration wurde entfernt: Sie hätte auch die
öffentlichen Einstiegsseiten serverseitig gesperrt und widersprach damit den
verbindlichen Zugangsregeln. Geschützte Humplore-Inhalte verwenden stattdessen
die Anwendungssession.

Diese Dateisperren setzen einen Apache-kompatiblen Server voraus, der
`.htaccess` auswertet. Der PHP-Entwicklungsserver tut dies nicht. Die Regeln
wurden am 31. August 2026 statisch geprüft, aber mangels lokaler Apache-Laufzeit
nicht als Apache-HTTP-Test abgenommen; vor einer Veröffentlichung ist diese
Serverabnahme noch erforderlich. Es wurde kein Live-/IONOS-System verändert.

## Prüfung

Die verbindliche Vorgehensweise steht in [`teststrategie.md`](teststrategie.md)
und in der `tasks.md` des jeweiligen OpenSpec-Changes.
