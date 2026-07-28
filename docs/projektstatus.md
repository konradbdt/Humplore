# Humplore Projektstatus

Stand: 28. Juli 2026

## Bewertungsbasis

- maßgeblicher Codebaum: `Webseite - Codex/`
- OpenSpec-Aufgaben und vorhandene Testartefakte
- manuelle Medienprüfung am 28. Juli 2026
- PHP-Syntaxprüfung zentraler Codex-Dateien am 28. Juli 2026

`Webseite - Redesign/` ist verworfen und wird für diesen Status nicht bewertet.

Statuswerte:

- **geprüft**: Implementierung und Prüfung sind belegt
- **implementiert**: Code ist vorhanden, vollständige Abnahme fehlt
- **teilweise**: nur ein abgegrenzter Teil ist umgesetzt
- **fehlerhaft**: Code ist vorhanden, das erwartete Verhalten funktioniert aber nicht zuverlässig
- **offen**: keine belastbare Implementierung gefunden

## Geprüft

| Bereich | Ergebnis | Detailquelle |
|---|---|---|
| Suche | Direkte Suche, fehlertolerante Erweiterung und Vorschläge sind im Codex-Code vorhanden. | `openspec/specs/search/spec.md` |
| Discovery-Basisfilter | Themen-, Themenkategorie- und Beitragskategorien sind im Codex-Code kombinierbar und zurücksetzbar. | `openspec/specs/discovery/spec.md` |
| Reporting für Fragen und Kommentare | Persistenz, Validierung, CSRF und Duplikatschutz sind vorhanden. | `openspec/specs/moderation/spec.md` |
| Profilfilter Stufe 1 | Wohnort und Sprache sind als Filterbereich implementiert. | `openspec/changes/profile-discovery-filters/tasks.md` |

## Implementiert, Abnahme offen

| Bereich | Vorhanden | Noch offen |
|---|---|---|
| Themen-/Kategorieübersicht | Browse-Bereich oberhalb des Explore-Feeds mit Themen, Kategorien, Beiträgen und Creatorn. | Manuelle Desktop-/Mobile-Abnahme. |
| Post-Aktionen | `Neues gelernt!`, `Kommentieren`, privates `Merken`, `Teilen`, `SavedPosts`, CSRF-Handler und synchronisierte Save-Buttons. | `search.php` verwendet eigene Karten ohne Action-Leiste; vollständiges interaktives Prüfprotokoll fehlt. |
| Authentifizierung | Registrierung, Login, Logout und Sessions sind vorhanden. | Account-Löschung, E-Mail-Verifizierung, Rate-Limits und Security-Review. |
| Creator-Profile | Profilinformationen, Beiträge, Fragen, Folgen und Teilen sind vorhanden. | Verifizierung, Vertrauenselemente, Direktnachrichten und Kommentarsteuerung. |

## Teilweise umgesetzt oder fehlerhaft

| Bereich | Tatsächlicher Stand im Codex-Code | Fehlender oder fehlerhafter Teil |
|---|---|---|
| Bild-Upload für Beiträge | `posten.php` akzeptiert Bilder; der Server speichert sie in `Posts.media_image`. | In Feed und Suche wird das Bild pauschal als JPEG ausgeliefert. PNG-, WebP- und GIF-Dateien können dadurch trotz Upload nicht korrekt erscheinen. Eine End-to-End-Abnahme fehlt. |
| Video-Upload | Keine funktionierende aktive Implementierung. | Die verlinkte Posting-Seite akzeptiert nur `image/*`; `post-editor.php` lehnt Videos ab. Die alte Route `create_post.php`/`process_post.php` ist nicht verlinkt und erwartet einen nicht vorhandenen `uploads/`-Ordner. |
| Profilfilter | Stufe 1 mit Wohnort und Übergangsfilter für Sprache ist fertig. | Stufe 2 mit normalisierten Sprachen, Altersgruppe, Herkunft, Identität und Einwilligungen: 21 OpenSpec-Tasks offen. |
| Reporting | Fragen und Kommentare sind meldbar. | Beiträge, Moderationsqueue, Entscheidungen, Audit-Trail, Verwarnung und Sperre. |
| Gemerkte Beiträge | Beiträge können privat gespeichert und entfernt werden. | Eigene Seite für gemerkte Beiträge. |
| Kommentare | Erstellen und Anzeigen funktioniert. | Antworten, Meldung auf allen Oberflächen, Likes, Deaktivierung durch Creator und Moderationswerkzeuge. |
| Folgen | Creator-Follow und ein Following-Modus sind vorhanden. | Themen-Follow, Benachrichtigungen und Übersicht gefolgter Creator. |

## Offen

### Launch-Blocker

- Creator-Verifizierung und Posting-Sperre für nicht verifizierte Creator.
- Admin-Moderationsdashboard und Reporting für Beiträge.
- Datenschutzseite, Löschkonzept und Account-Löschung.
- Anonyme Fragen sowie anonyme Creator-Beiträge.
- Direktnachrichten.
- Eigene Seite für gemerkte Beiträge.
- Kommentar-Deaktivierung durch Creator.
- Vollständige mobile, funktionale und Accessibility-Abnahme.

### Technische Launch-Arbeit

- Bild-Upload und Bildanzeige in Profil, Explore und Suche end-to-end reparieren und abnehmen.
- Video-Upload erst nach einer eigenen Spezifikation implementieren.
- Performance-Baseline und Lasttests für Explore, Suche und Profile.
- Entscheidung und Migrationsplan für SQLite oder MySQL.
- Backup-/Restore-Prozess, einheitliches Error-Handling und Login-Rate-Limiting.

### Später

- Monetarisierung und Donation.
- Datenexport, Profilmeldungen, Feed-Personalisierung, Echtzeitfunktionen,
  PWA, Analytics und native App.

## Bekannte Risiken und Inkonsistenzen

1. **Verworfener zweiter Codebaum:** `Webseite - Redesign/` liegt noch im
   Repository, ist aber keine Arbeitsgrundlage. Er darf nicht wieder als
   Referenz für Status oder Tests verwendet werden.
2. **Medienlogik ist uneinheitlich:** Aktive und alte Posting-Routen verwenden
   unterschiedliche Speicherwege; die alte Video-Route ist nicht integriert.
3. **Unvollständige Regression:** Ein historischer Test vom 14. Juni 2026
   protokolliert zwei 30-Sekunden-Timeouts beim Plattformaufruf. Eine vollständige
   Wiederholung fehlt.
4. **Zeichenkodierung:** Mehrere aktive PHP-Dateien enthalten fehlerhaft
   dekodierte Zeichenfolgen.
5. **Lokale Testdaten:** Manuelle Tests verändern die SQLite-Datei
   `Webseite - Codex/data/database.db`; Testdaten dürfen nicht unbeabsichtigt
   als Produktdaten committed werden.
6. **OpenSpec-CLI:** Die CLI ist in dieser Umgebung nicht installiert. Die
   OpenSpec-Struktur wurde deshalb manuell geprüft.

## Letzte technische Prüfung

`php -l` war am 28. Juli 2026 fehlerfrei für:

- `posten.php`
- `app/support/post-editor.php`
- `platform.php`
- `profile.php`
- `search.php`
- `media.php`
- `process_post.php`
