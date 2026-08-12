# Humplore Projektstatus

Stand: 12. August 2026

## Bewertungsbasis

- maßgeblicher Codebaum: `Webseite - Codex/`
- OpenSpec-Aufgaben und vorhandene Testartefakte
- manuelle Medienprüfung am 28. Juli 2026
- isolierte Browser- und Handlerprüfung der Post-Aktionen am 11. August 2026
- PHP-Syntaxprüfung der geänderten Post-Aktionsdateien am 11. August 2026
- isolierte Schema-, Handler- und Browserprüfung anonymer Fragen am 12. August 2026

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
| Post-Aktionen | `Neues gelernt!`, `Kommentieren`, privates `Merken` und `Teilen` sind in Explore, eigenständiger Suche, Profil und Modal geprüft; Handler-, CSRF-, Gast-, Sperr- und Mobilfälle sind belegt. | `openspec/specs/engagement/spec.md` |
| Anonyme Fragen | Angemeldete Nutzer können pro Frage Anonymität wählen; interne Attribution bleibt erhalten, Creator- und Besucheransichten sowie Frage-als-Beitrag schützen die Identität. Schema-, Handler-, Desktop- und Mobile-Prüfungen sind belegt. | `openspec/specs/questions/spec.md` |

## Implementiert, Abnahme offen

| Bereich | Vorhanden | Noch offen |
|---|---|---|
| Themen-/Kategorieübersicht | Browse-Bereich oberhalb des Explore-Feeds mit Themen, Kategorien, Beiträgen und Creatorn. | Manuelle Desktop-/Mobile-Abnahme. |
| Authentifizierung | Registrierung, Login, Logout und Sessions sind vorhanden. | Account-Löschung, E-Mail-Verifizierung, Rate-Limits und Security-Review. |
| Creator-Profile | Profilinformationen, Beiträge, Fragen, Folgen und Teilen sind vorhanden. Creator können unabhängig von einer Verifizierung veröffentlichen. | Optionaler Verifizierungsprozess, Badge und Ranking-Vorteil sowie Direktnachrichten und Kommentarsteuerung. |

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

## Funktionsabgleich mit den Produktanalysen

Funktionskatalog und Sollbild stammen ausschliesslich aus diesen beiden
historischen Produktanalysen:

- `docs/archive/asciidoc-stand-2026-07-24/product/humplore_funktionsanalyse_bisher.adoc`
- `docs/archive/asciidoc-stand-2026-07-24/product/website_function_gap_analysis.adoc`

Die Aussagen dort zum damaligen Stand und zu Luecken sind veraltet. Die
folgende Neubewertung prueft deshalb die genannten Funktionen gegen den
aktiven Codebaum `Webseite - Codex/`. Sie basiert grundsätzlich auf der Code-
und Dokumentenprüfung vom 28. Juli 2026; die Beitragsaktionen wurden zusätzlich
am 11. August 2026 vollständig im Browser und auf Handler-Ebene geprüft.

| Funktion aus den Analysen | Neubewerteter Stand im aktiven Projekt | Noch zu pruefen oder umzusetzen |
|---|---|---|
| Themenbasierte Suche | implementiert | Direktsuche, fehlertolerante Suche, Vorschlaege und Leerzustand sind vorhanden. Eine aktuelle Browser-Abnahme mit realistischen Daten fehlt. |
| Filter und Sortierung | teilweise | Themenkategorie, Thema, Beitrags-/Lebenskategorie, Wohnort, Sprache sowie Neueste/Beliebt sind vorhanden. Filter fuer Beruf, Alter und Erfahrungstyp fehlen; die vollstaendige Kombination der vorhandenen Filter ist manuell zu pruefen. |
| Themen- und Kategorieuebersicht | implementiert, Abnahme offen | Funktionaler Browse-Bereich mit getrennten Themen- und Beitrags-/Lebenskategorien sowie Feed-Links ist vorhanden. Desktop- und Mobilansicht sind noch manuell abzunehmen. |
| Creator-Registrierung | teilweise | Registrierung erfasst Creator-Status und Hauptthema. Eine strukturierte Kategorienzuordnung waehrend der Registrierung fehlt. |
| Creator-Profil und Profilbearbeitung | teilweise | Profilbild und Bio sind bearbeitbar; Profile zeigen unter anderem Hauptthema, Ort/Sprache, Beitraege und Follow. Bearbeitbare Felder fuer Ort, Alter und Fokus sowie strukturierte Unterthemen/Tags fehlen. |
| Creator-Verifizierung und Vertrauenselemente | offen | Kein Verifizierungsprozess, kein Badge und keine bevorzugte Ausspielung verifizierter Creator. Eine Posting-Sperre ist ausdruecklich nicht vorgesehen. |
| Anonyme Nutzung und Privatsphaere | teilweise | Pseudonyme Nutzung ist durch frei waehbaren Nutzernamen moeglich; anonyme Fragen sind geprüft implementiert. Anonyme Beitraege und steuerbare Profilsichtbarkeit fehlen. |
| Fragen und Antworten an Creator | geprüft | Normale und anonyme Fragen koennen gestellt und vom Creator kurz beantwortet oder ohne Veröffentlichung der Fragesteller-Identität in einen Beitrag uebernommen werden. Schema-, Handler-, Desktop- und Mobile-Fälle sind belegt. |
| Beitragsaktionen und Merken | geprüft | `Neues gelernt!`, Kommentieren, privates Merken und Teilen sind in Explore, eigenständiger Suche, Profil und Modal interaktiv geprüft. Suche verwendet die gemeinsame Plattform-Postkarte und Bulk-Lookups. Eine Seite für gemerkte Beiträge und Donation bleiben bewusst außerhalb dieses Changes. |
| Folgen | teilweise | Creator koennen gefolgt werden; ein Following-Modus ist vorhanden. Themen folgen, Benachrichtigungen und eine eigene Uebersicht gefolgter Creator fehlen. |
| Kommentare und Austausch | teilweise | Kommentare koennen erstellt und angezeigt werden; Kommentare sind meldbar. Antworten auf Kommentare, Kommentar-Likes, Creator-Steuerung zum Deaktivieren und Moderationswerkzeuge fehlen. |
| Melden und Safe-Space-Funktionen | teilweise | Fragen und Kommentare koennen mit Validierung, CSRF- und Duplikatschutz gemeldet werden. Beitraege und Profile sind nicht meldbar; Moderationsqueue, Entscheidungen, Verwarnungen, Sperren und Blockieren fehlen. |
| News und Benachrichtigungen | offen | `news.php` ist eine statische News-Seite; Interaktionsbenachrichtigungen, ungelesene Zustaende und ein Ereignisfeed sind nicht implementiert. |
| Direktnachrichten | offen | Keine Nachrichtenfunktion zwischen Usern und Creatorn gefunden. |
| Bild-, Video- und Audio-Beitraege | fehlerhaft / offen | Die aktive Posting-Seite akzeptiert nur Bilder. PNG, WebP und GIF koennen trotz Upload im Feed bzw. in der Suche wegen einer pauschalen JPEG-Auslieferung fehlerhaft erscheinen. Video und Audio sind nicht implementiert. |
| Themenuebergreifende Unterkategorien ("Rabbit-hole") | teilweise | Beitrags-/Lebenskategorien lassen sich bereits themenuebergreifend filtern. Ein eigenes, strukturiertes Unterkategorie- bzw. Tag-Modell mit der beschriebenen Navigation fehlt. |
| Personalisierter Feed | offen / zurueckgestellt | Kein personalisierter Feed auf Basis von Folgen, Interessen oder gespeicherten Beitraegen. |

### Noch auszufuehrende manuelle Funktionsabnahme

1. Suche: exakter Begriff, Tippfehler, Vorschlag und Leerzustand.
2. Explore: alle vorhandenen Filter einzeln und kombiniert, Sortierung sowie
   vollstaendiges Zuruecksetzen.
3. Browse-Uebersicht: Anzeige ohne Filter sowie Desktop- und Mobilansicht.
4. Registrierung, Login, Creator-Profil, Folgen und Profilbearbeitung mit zwei
   Testkonten.
5. Melden: Frage und Kommentar melden, Doppelmeldung, ungueltige Daten und
   nicht angemeldeter Aufruf.
6. Medien: JPEG, PNG und WebP posten und in Explore, Suche und Profil anzeigen;
   Video und Audio weiterhin als nicht implementiert festhalten.

## Offen

### Launch-Blocker

- Admin-Moderationsdashboard und Reporting für Beiträge.
- Datenschutzseite, Löschkonzept und Account-Löschung.
- Anonyme Creator-Beiträge.
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

- Optionale Creator-Verifizierung mit Badge und nachvollziehbarem
  Ranking-Vorteil in Suche und Discovery.
- Monetarisierung und Donation.
- Datenexport, Profilmeldungen, Feed-Personalisierung, Echtzeitfunktionen,
  PWA, Analytics und native App.

## Bekannte Risiken und Inkonsistenzen

1. **Verworfener zweiter Codebaum:** `Webseite - Redesign/` ist im aktuellen
   Repository nicht mehr vorhanden und darf nicht wieder als Referenz für
   Status oder Tests angenommen werden.
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

Am 12. August 2026 waren die Schemaerweiterung und ihre Wiederholung, normale
und anonyme Speicherung, interne `author_id`, Erfolgstexte, manipulierte
Formularwerte, unveränderliche Anonymität, Gast-Sperre, Creator- und
Besucheransichten sowie normale und anonyme Frage-als-Beitrag-Flows auf einer
isolierten SQLite-Kopie erfolgreich. `php -l` war für alle acht geänderten
PHP-Dateien fehlerfrei. Die Browserprüfung bei 1440x900 und 390x844 bestätigte
Formular, Checkbox-Default, 44px-Mobile-Control, Identitätsschutz und fehlenden
horizontalen Overflow; Browser-Logs enthielten keine Fehler oder Warnungen.
Die Repository-Datei `Webseite - Codex/data/database.db` blieb unverändert.
