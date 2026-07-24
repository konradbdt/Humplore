# Humplore Projektstatus

Stand: 24. Juli 2026

Bewertungsbasis:

- sichtbarer Code in `Webseite - Redesign/`
- OpenSpec-Aufgaben und vorhandene Testartefakte
- PHP-Syntaxprüfungen zentraler Dateien am 24. Juli 2026
- Vergleich mit `Webseite - Codex/`
- Git-Stand auf `main` bei `99901087ff38`

Statuswerte:

- **geprüft**: implementiert und mit dokumentierter Prüfung belegt
- **implementiert**: Code vorhanden, vollständige Abnahme noch offen
- **teilweise**: nur ein abgegrenzter Teil ist umgesetzt
- **offen**: keine belastbare Implementierung gefunden

## Geprüft

| Bereich | Ergebnis | Verbindliche Detailquelle |
|---|---|---|
| Suche | Direkte Suche, fehlertolerante Erweiterung und Vorschläge sind umgesetzt. | `openspec/specs/search/spec.md` |
| Discovery-Basisfilter | Themen-, Themenkategorie- und Beitragskategorien lassen sich kombinieren und zurücksetzen. | `openspec/specs/discovery/spec.md` |
| Reporting für Fragen und Kommentare | Persistenz, Validierung, CSRF, Duplikatschutz und UI sind umgesetzt; Inhalte bleiben sichtbar. | `openspec/specs/moderation/spec.md` |
| Profilfilter Stufe 1 | Wohnort und Sprache sind als eigener Filterbereich umgesetzt; Suche und Feed berücksichtigen sie. | `openspec/changes/profile-discovery-filters/tasks.md` |

## Implementiert, Abnahme offen

| Bereich | Vorhanden | Noch offen |
|---|---|---|
| Themen-/Kategorieübersicht | Browse-Bereich oberhalb des Explore-Feeds mit Themen, Kategorien, Beiträgen und Creatorn. | Manuelle Desktop-/Mobile-Abnahme. |
| Post-Aktionen | `Neues gelernt!`, `Kommentieren`, privates `Merken`, `Teilen`, `SavedPosts`, CSRF-Handler und synchronisierte Save-Buttons. | Die eigenständige `search.php` verwendet noch eigene Karten ohne Action-Leiste; vollständiges interaktives Prüfprotokoll fehlt. |
| Landingpage-Redesign | `Webseite - Redesign/index.html` wurde am 24. Juli 2026 neu gestaltet; weitere öffentliche Seiten wurden an die gemeinsame Darstellung angebunden. | Browserprüfung über relevante Breakpoints und Inhalte. |
| Video-Basis | Uploadfeld akzeptiert Videos und `process_post.php` erkennt Videoformate. | Dateilimits, sichere Speicherung, Vorschau, Transcoding, Moderation und mobile Wiedergabe sind nicht abgenommen. |
| Authentifizierung | Registrierung, Login, Logout und Sessions sind vorhanden. | Account-Löschung, E-Mail-Verifizierung, Rate-Limits und vollständiger Security-Review. |
| Creator-Profile | Profilinformationen, Beiträge, Fragen, Folgen und Teilen sind vorhanden. | Verifizierungsstatus, Vertrauenselemente, Direktnachrichten und Kommentarsteuerung. |

## Teilweise umgesetzt

| Bereich | Erledigter Teil | Fehlender Teil |
|---|---|---|
| Profilfilter | Stufe 1 mit Wohnort und Übergangsfilter für Sprache ist fertig. | Stufe 2 mit normalisierten Sprachen, Altersgruppe, Herkunft, Identität und Einwilligungen: 21 OpenSpec-Tasks offen. |
| Reporting | Fragen und Kommentare sind meldbar. | Beiträge, Moderationsqueue, Entscheidungen, Audit-Trail, Verwarnung und Sperre. |
| Gemerkte Beiträge | Beiträge können privat gespeichert und wieder entfernt werden. | Eigene Seite für gemerkte Beiträge. |
| Kommentare | Erstellen und Anzeigen funktioniert. | Antworten, Melden auf allen Oberflächen, Likes, Deaktivierung durch Creator und Moderationswerkzeuge. |
| Folgen | Creator-Follow und ein Following-Modus sind vorhanden. | Themen-Follow, Benachrichtigungen und Übersicht gefolgter Creator. |

## Offen

### Launch-Blocker

- Creator-Verifizierung und Posting-Sperre für nicht verifizierte Creator.
- Admin-Moderationsdashboard.
- Reporting für Beiträge inklusive neuer Meldegründe.
- Datenschutzseite und belastbares Löschkonzept.
- Account-Löschung.
- Anonyme Fragen.
- Anonyme Creator-Beiträge und globale Anonymitätseinstellung.
- Direktnachrichten.
- Eigene Seite für gemerkte Beiträge.
- Kommentar-Deaktivierung durch Creator.
- Vollständige mobile und funktionale Abnahme.

### Technische Launch-Arbeit

- Performance-Baseline und Lasttests für Explore, Suche und Profile.
- Entscheidung und Migrationsplan für SQLite oder MySQL.
- Medienstrategie für Bilder und Videos.
- Backup-/Restore-Prozess.
- Einheitliches Error-Handling und Login-Rate-Limiting.
- Accessibility-Prüfung einschließlich Tastaturbedienung, Fokusführung,
  Kontrast und Touch-Zielen.

### Später

- Monetarisierung und Donation.
- Datenexport.
- Profilmeldungen.
- Vollständige Feed-Personalisierung.
- Echtzeitfunktionen, PWA, Analytics und native App.

## Bekannte Risiken und Inkonsistenzen

1. **Zwei Codebäume:** `Webseite - Redesign/` und `Webseite - Codex/` enthalten
   je 82 relevante Dateien; 14 Dateien unterscheiden sich. Der Redesign-Baum
   ist neuer, eine verbindliche Konsolidierung fehlt.
2. **Unkonsolidierter Git-Arbeitsbaum:** Git ist wieder nutzbar (`main`,
   `99901087ff38`, Remote `origin` vorhanden). Der Arbeitsbaum enthält jedoch
   die aktuelle Dokumentbereinigung sowie weitere Änderungen und unversionierte
   Dateien. Ein sauberer, bestätigter Basis-Commit für den Redesign-Stand fehlt.
3. **Unvollständige Regression:** Ein historischer Regressionstest vom
   14. Juni 2026 protokolliert zwei 30-Sekunden-Timeouts beim Plattformaufruf.
   Spätere Requests auf Like, Save und Profile waren erfolgreich; eine saubere
   vollständige Wiederholung fehlt.
4. **Zeichenkodierung:** In mehreren aktiven PHP-Dateien sind fehlerhaft
   dekodierte Zeichenfolgen sichtbar.
5. **OpenSpec-Werkzeug:** Die OpenSpec-CLI ist in der aktuellen Umgebung nicht
   installiert. Die Dokumente wurden deshalb strukturell manuell geprüft.

## Letzte technische Prüfung

`php -l` war am 24. Juli 2026 für folgende Dateien fehlerfrei:

- `search.php`
- `platform.php`
- `profile.php`
- `save_post_handler.php`
- `report_handler.php`
- `app/support/search-discovery.php`
- `app/support/platform-page.php`
- `app/support/reports.php`
