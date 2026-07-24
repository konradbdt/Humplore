# Projektstruktur

## Aktive Bereiche

- `config/`: zentrale Laufzeitkonfiguration, aktuell die SQLite-Verbindung.
- `css/`: globale Stylesheets.
- `data/`: Laufzeitdaten wie die SQLite-Datenbank.
- `inc/`: Rueckwaertskompatible Include-Einstiegspunkte.
- `app/`: neue interne Struktur fuer gemeinsame Bootstrap-, Support- und Partial-Logik.
- Root-Dateien wie `platform.php`, `profile.php`, `posten.php`, `news.php`: bestehende oeffentliche Routen bleiben unveraendert.

## Neue interne Struktur

- `app/bootstrap.php`: gemeinsamer Einstieg fuer Session, Zeitzone und Support-Funktionen.
- `app/support/`: wiederverwendbare Helper fuer Escaping, Auth, Navigation, CSRF sowie Content-/Profil-/Profil-Action-/Plattform-/Post-Editor-Helfer.
- `app/views/partials/`: gemeinsam genutzte Templates fuer Navigation, Plattformkarten sowie Profile-Header, Q&A, Modals und Post-Cards.

## Archiv und temporaere Dateien

- `legacy/`: bewusst ausgegliederte Backup- und Experimentdateien, die nicht mehr im Root stoeren sollen.
- `tmp/`: lokale Testartefakte und Server-Logs.

## Ziel

Die bestehenden URLs und die sichtbare Ausgabe bleiben erhalten, waehrend interne Logik und Altlasten klar voneinander getrennt sind.
