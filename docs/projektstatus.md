# Humplore Projektstatus

Stand: 31. August 2026

## Bewertungsbasis

- maßgeblicher Codebaum: `Webseite - Codex/`
- OpenSpec-Aufgaben und vorhandene Testartefakte
- lokale HTTP- und Funktionstests der aktiven Medienlogik am 18. August 2026
- PHP-Syntaxprüfung der betroffenen PHP-Dateien am 18. August 2026
- manuelle Medienprüfung am 28. Juli 2026
- isolierte Browser- und Handlerprüfung der Post-Aktionen am 11. August 2026
- PHP-Syntaxprüfung der geänderten Post-Aktionsdateien am 11. August 2026
- isolierte Schema-, Handler- und Browserprüfung anonymer Fragen am 12. August 2026
- isolierte Funktions- und Browserprüfung der kontextbezogenen Suchmarkierung am 18. August 2026
- isolierte HTTP-, Login-, Handler- und Redirectprüfung des PHP-Routenschutzes am 31. August 2026
- PHP-Syntaxprüfung aller 20 geänderten PHP-Dateien und statische Prüfung der Apache-Dateisperren am 31. August 2026

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
| Intelligente Suchmarkierung und passende Fragen | Wörtliche und verwandte Treffer werden sicher und nachvollziehbar markiert; die Fragenleiste und ihr Modal verwenden bei aktiver Suche dieselbe gefilterte Datenbasis. PHP-, Funktions-, Desktop- und Mobile-Prüfungen sind belegt. | `openspec/changes/contextual-search-highlighting/tasks.md` |
| PHP-Routenzugriffsschutz | Öffentliche Einstiege, Login-Redirects, JSON-401, Medien-401, Creator-403, deaktivierte Alt-/Diagnoserouten-404, Handler-405 sowie Login/Logout und Handler-Erfolgsläufe sind isoliert über HTTP geprüft. Die ergänzenden Apache-Dateisperren sind implementiert, ihre Serverabnahme ist noch offen. | `docs/architektur.md`, Abschnitt Routenzugriffsschutz |

## Implementiert, Abnahme offen

| Bereich | Vorhanden | Noch offen |
|---|---|---|
| Themen-/Kategorieübersicht | Browse-Bereich oberhalb des Explore-Feeds mit Themen, Kategorien, Beiträgen und Creatorn. | Manuelle Desktop-/Mobile-Abnahme. |
| Authentifizierung | Registrierung, Login, Logout und Sessions sind vorhanden. Aktive PHP-Anwendungs- und Medienrouten verlangen eine Anmeldung; nicht produktive Root-Einstiege sind gesperrt. | Apache-Abnahme der internen Dateisperren sowie Account-Löschung, E-Mail-Verifizierung, Rate-Limits und übergreifender Security-Review. |
| Creator-Profile | Profilinformationen, Beiträge, Fragen, Folgen und Teilen sind vorhanden. Creator können unabhängig von einer Verifizierung veröffentlichen. | Optionaler Verifizierungsprozess, Badge und Ranking-Vorteil sowie Direktnachrichten und Kommentarsteuerung. |

## Teilweise umgesetzt oder fehlerhaft

| Bereich | Tatsächlicher Stand im Codex-Code | Fehlender oder fehlerhafter Teil |
|---|---|---|
| Bild-Upload für Beiträge | Die aktive Route `posten.php` akzeptiert ausschließlich JPEG, PNG und WebP bis 5 MB. Uploadfehler, tatsächliche Dateigröße und Bildformat werden serverseitig geprüft; Feed/Explore, Suche und Creator-Profile laden Beitragsbilder über `media.php` mit erkanntem MIME-Type. JPEG und PNG wurden als echte HTTP-Uploads geprüft, WebP wurde mit einem gültigen In-Memory-Testbild gegen die verwendete MIME-Erkennung geprüft. Ungültige, leere, unvollständige und zu große Dateien sowie eine manipulierte Client-MIME-Angabe wurden als Fehlerfälle geprüft. | Ein authentifizierter Browser-End-to-End-Test mit neuem JPEG-, PNG- und WebP-Beitrag und anschließender Anzeige in Explore, Suche und Creator-Profil steht noch aus. Das lokale PHP-Limit `upload_max_filesize=2M` muss für die zugesagten 5 MB in der Zielumgebung auf mindestens 5 MB angehoben werden. GIF ist für neue Beitrags-Uploads nicht freigegeben. |
| Video-/Audio-Upload | Keine funktionierende aktive Implementierung; die aktive Posting-Seite bleibt auf JPEG, PNG und WebP beschränkt. | Video- und Audio-Upload sind weiterhin offen. Die alte, nicht verlinkte Upload-Route ist keine Grundlage der aktiven Implementierung. |
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
| Themenbasierte Suche | geprüft | Direktsuche, fehlertolerante Suche, Vorschläge und Leerzustand sind vorhanden. Wörtliche und verwandte Markierung sowie passende Fragen wurden am 18. August 2026 mit realistischen Daten im Browser geprüft. |
| Filter und Sortierung | teilweise | Themenkategorie, Thema, Beitrags-/Lebenskategorie, Wohnort, Sprache sowie Neueste/Beliebt sind vorhanden. Filter fuer Beruf, Alter und Erfahrungstyp fehlen; die vollstaendige Kombination der vorhandenen Filter ist manuell zu pruefen. |
| Themen- und Kategorieuebersicht | implementiert, Abnahme offen | Funktionaler Browse-Bereich mit getrennten Themen- und Beitrags-/Lebenskategorien sowie Feed-Links ist vorhanden. Desktop- und Mobilansicht sind noch manuell abzunehmen. |
| Creator-Registrierung | teilweise | Registrierung erfasst Creator-Status und Hauptthema. Eine strukturierte Kategorienzuordnung waehrend der Registrierung fehlt. |
| Creator-Profil und Profilbearbeitung | teilweise | Profilbild und Bio sind bearbeitbar; Profile zeigen unter anderem Hauptthema, Ort/Sprache, Beitraege und Follow. Bearbeitbare Felder fuer Ort, Alter und Fokus sowie strukturierte Unterthemen/Tags fehlen. |
| Creator-Verifizierung und Vertrauenselemente | offen | Kein Verifizierungsprozess, kein Badge und keine bevorzugte Ausspielung verifizierter Creator. Eine Posting-Sperre ist ausdruecklich nicht vorgesehen. |
| Anonyme Fragen und pseudonyme Creator | geprüft / implementiert | Anonyme Fragen sind geprüft implementiert. Creator koennen bereits einen frei waehlbaren Nutzernamen verwenden; Beitraege bleiben dem jeweiligen oeffentlichen Profil zugeordnet. Anonyme Beitraege sind bewusst nicht vorgesehen. |
| Fragen und Antworten an Creator | geprüft | Normale und anonyme Fragen koennen gestellt und vom Creator kurz beantwortet oder ohne Veröffentlichung der Fragesteller-Identität in einen Beitrag uebernommen werden. Schema-, Handler-, Desktop- und Mobile-Fälle sind belegt. |
| Beitragsaktionen und Merken | geprüft | `Neues gelernt!`, Kommentieren, privates Merken und Teilen sind in Explore, eigenständiger Suche, Profil und Modal interaktiv geprüft. Suche verwendet die gemeinsame Plattform-Postkarte und Bulk-Lookups. Eine Seite für gemerkte Beiträge und Donation bleiben bewusst außerhalb dieses Changes. |
| Folgen | teilweise | Creator koennen gefolgt werden; ein Following-Modus ist vorhanden. Themen folgen, Benachrichtigungen und eine eigene Uebersicht gefolgter Creator fehlen. |
| Kommentare und Austausch | teilweise | Kommentare koennen erstellt und angezeigt werden; Kommentare sind meldbar. Antworten auf Kommentare, Kommentar-Likes, Creator-Steuerung zum Deaktivieren und Moderationswerkzeuge fehlen. |
| Melden und Safe-Space-Funktionen | teilweise | Fragen und Kommentare koennen mit Validierung, CSRF- und Duplikatschutz gemeldet werden. Beitraege und Profile sind nicht meldbar; Moderationsqueue, Entscheidungen, Verwarnungen, Sperren und Blockieren fehlen. |
| News und Benachrichtigungen | offen | `news.php` ist eine statische News-Seite; Interaktionsbenachrichtigungen, ungelesene Zustaende und ein Ereignisfeed sind nicht implementiert. |
| Direktnachrichten | offen | Keine Nachrichtenfunktion zwischen Usern und Creatorn gefunden. |
| Bild-, Video- und Audio-Beitraege | Bildfunktion implementiert, Browser-Abnahme offen; Video/Audio offen | Die aktive Posting-Seite akzeptiert ausschließlich JPEG, PNG und WebP. Beitragsbilder werden in Explore, Suche und Creator-Profilen über `media.php` mit serverseitig erkanntem MIME-Type ausgeliefert. Der authentifizierte Browser-End-to-End-Test aller drei Formate steht noch aus. Video und Audio sind nicht implementiert. |
| Themenuebergreifende Unterkategorien ("Rabbit-hole") | teilweise | Beitrags-/Lebenskategorien lassen sich bereits themenuebergreifend filtern. Ein eigenes, strukturiertes Unterkategorie- bzw. Tag-Modell mit der beschriebenen Navigation fehlt. |
| Personalisierter Feed | offen / zurueckgestellt | Kein personalisierter Feed auf Basis von Folgen, Interessen oder gespeicherten Beitraegen. |

### Noch auszufuehrende manuelle Funktionsabnahme

1. Explore: alle vorhandenen Filter einzeln und kombiniert, Sortierung sowie
   vollstaendiges Zuruecksetzen.
2. Browse-Uebersicht: Anzeige ohne Filter sowie Desktop- und Mobilansicht.
3. Registrierung, Login, Creator-Profil, Folgen und Profilbearbeitung mit zwei
   Testkonten.
4. Melden: Frage und Kommentar melden, Doppelmeldung, ungueltige Daten und
   nicht angemeldeter Aufruf.
5. Medien: authentifiziert je einen JPEG-, PNG- und WebP-Beitrag posten und in
   Explore, Suche und Creator-Profil anzeigen; Video und Audio weiterhin als
   nicht implementiert festhalten.

## Offen

### Launch-Blocker

- Admin-Moderationsdashboard und Reporting für Beiträge.
- Datenschutzseite, Löschkonzept und Account-Löschung.
- Direktnachrichten.
- Eigene Seite für gemerkte Beiträge.
- Kommentar-Deaktivierung durch Creator.
- Vollständige mobile, funktionale und Accessibility-Abnahme.

### Technische Launch-Arbeit

- Apache-HTTP-Abnahme der `.htaccess`-Sperren für interne Verzeichnisse,
  Backups, Testartefakte und sensible Dateien vor einer Veröffentlichung.
- Authentifizierte Browser-End-to-End-Abnahme für JPEG, PNG und WebP in
  Creator-Profil, Explore und Suche durchführen.
- PHP-Uploadlimit der Zielumgebung auf mindestens 5 MB abstimmen.
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

1. **Alte Medienlogik:** Nicht integrierter Uploadcode liegt noch in den
   deaktivierten Alt-Routen; die aktive Anwendung verwendet ausschließlich
   `posten.php` und `media.php`. Video-Uploads bleiben offen.
2. **PHP-Uploadlimit:** Die lokale PHP-Konfiguration begrenzt Uploads auf 2 MB,
   obwohl die Anwendung Bilder bis 5 MB vorsieht. Die Zielumgebung muss dafür
   passend konfiguriert werden.
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
7. **Apache-Dateisperren noch nicht im Serverbetrieb abgenommen:** Die Regeln
   für interne Verzeichnisse und sensible Dateien sind vorhanden und statisch
   geprüft. Lokal steht kein Apache bereit; der PHP-Entwicklungsserver wertet
   `.htaccess` nicht aus. Die erfolgreiche PHP-Routenprüfung ist deshalb keine
   Apache- oder IONOS-Abnahme.

## Letzte technische Prüfung

Am 31. August 2026 wurde der Zugriffsschutz auf einer temporären Kopie des
Webroots mit isolierter SQLite-Kopie und zwei eigenen Testkonten geprüft
(PHP 8.3.29, lokaler HTTP-Server auf `127.0.0.1:8873`). Es wurden keine
Live-/IONOS-Aufrufe durchgeführt.

- `php -l` war für alle 20 geänderten PHP-Dateien fehlerfrei.
- Die sieben vorhandenen öffentlichen Einstiegs-, Auth- und Informationsseiten
  lieferten ohne Humplore-Anmeldung HTTP 200.
- `platform.php`, `search.php`, `profile.php?debug=ping`, `posten.php`,
  `news.php` und `fragen.php?creator_id=910002` lieferten als Gast 302 zum Login
  mit lokalem Rückkehrziel; geschützte Inhalte wurden nicht ausgegeben.
- `media.php` lieferte als Gast 401 ohne Redirect und ohne Bilddaten. Die drei
  JSON-Handler lieferten für Gast-POSTs 401 als JSON, ohne Location-Header.
- Die acht deaktivierten Root-Einstiege aus der Architekturmatrix lieferten
  404 mit ausschließlich `Not found.`; auch der angemeldete Aufruf von
  `create_post.php` blieb gesperrt.
- Login als Mitglied und Creator war erfolgreich. Explore, Suche, Profil,
  News und Fragen lieferten angemeldet 200. `posten.php` lieferte für das
  Mitglied 403 und für den Creator 200. Die doppelte lokale Definition des
  Escaping-Helfers in `news.php` wurde durch den gemeinsamen Helfer ersetzt.
- Das isolierte PNG-Profilbild wurde angemeldet mit 200, `image/png` und
  `private, no-store` ausgeliefert. POST auf die Medienroute lieferte 405.
  `profile.php?debug=trace` zeigte nur die normale Profilseite und keine
  Diagnosemarker; ein ungültiger Medienaufruf mit `debug=1` blieb ohne
  technische Fehlerdetails.
- Authentifizierte Handler mit gültigem CSRF-Token erreichten ihre
  Parameterprüfung (400). Gültige Like-, Merken- und Melden-Aufrufe lieferten
  200; die isolierte Datenbank enthielt jeweils genau einen zugehörigen
  Datensatz. Eine wiederholte Meldung blieb idempotent.
- GET auf die beiden öffentlichen Auth-Handler sowie auf den angemeldeten
  Reporting-Handler lieferte 405. Ein lokales Login-Rückkehrziel blieb erhalten,
  ein externes Ziel wurde verworfen. Nach Logout war Explore wieder gesperrt.
- Die PHP-Serverlogs enthielten keine PHP-Warnungen oder Laufzeitfehler.
  Dies war eine HTTP-/Sessionprüfung, keine neue Desktop-/Mobile-Browserabnahme.
- Die Repository-Datenbank blieb unverändert; ihr SHA-256 war vor und nach
  den Tests `67909AB8B677155BD70D3E25D83C034CA468425708CAA032D1A0BCB8C012782D`.

Die Root-`.htaccess` und die sechs internen Verzeichnissperren wurden nur
statisch geprüft. Eine ausführbare Apache-Konfigurations-/HTTP-Prüfung und
eine IONOS-Abnahme wurden ausdrücklich nicht durchgeführt.

Am 31. August 2026 wurde außerdem die veraltete globale HTTP-Basic-Auth aus
der Root-`.htaccess` entfernt. Sie hätte auch Einstieg, Registrierung und Login
vor der Humplore-Anmeldung gesperrt. Die Dateisperren bleiben bestehen; ihre
Apache-Abnahme ist weiterhin offen.

`php -l` war am 18. August 2026 fehlerfrei für:

- `posten.php`
- `app/support/post-editor.php`
- `platform.php`
- `profile.php`
- `search.php`
- `media.php`
- `process_post.php`

Am 12. August 2026 waren die Schemaerweiterung und ihre Wiederholung, normale
und anonyme Speicherung, interne `author_id`, Erfolgstexte, manipulierte
Formularwerte, unveränderliche Anonymität, Gast-Sperre, Creator- und
Besucheransichten sowie normale und anonyme Frage-als-Beitrag-Flows auf einer
isolierten SQLite-Kopie erfolgreich. `php -l` war für alle acht geänderten
PHP-Dateien fehlerfrei. Die Browserprüfung bei 1440x900 und 390x844 bestätigte
Formular, Checkbox-Default, 44px-Mobile-Control, Identitätsschutz und fehlenden
horizontalen Overflow; Browser-Logs enthielten keine Fehler oder Warnungen.
Die Repository-Datei `Webseite - Codex/data/database.db` blieb unverändert.

Am 18. August 2026 wurde `contextual-search-highlighting` auf einer isolierten
Kopie der SQLite-Datenbank geprüft. `Krebs` ergab 1 Profil, 10 Beiträge und
1 passende Frage; die eigenständige Suche zeigte 38 und Explore 40 sichtbare
Markierungen. `Krebz` aktivierte die fehlertolerante Suche, markierte die
tatsächlich vorkommenden verwandten Begriffe und zeigte die Kennzeichnung
`Thematisch verwandt`. Eine Suche ohne passende Frage zeigte ausschließlich
`Keine passenden Fragen gefunden.`; ohne Suchanfrage blieb `Gestellte Fragen`
mit 28 Fragen und ohne Markierungen erhalten. HTML-artige Eingaben und Inhalte
wurden als Text ausgegeben, ohne ausführbares Markup zu erzeugen. Die
Browserprüfung bei 1440x900 und 390x844 bestätigte fehlenden horizontalen
Overflow, sichtbare Markierungen mit gelbem Hintergrund `#ffe066` und dunkler
Schrift `#241d00`, identische Fragedaten in Leiste und Modal sowie fehlerfreie
Browserlogs. Filterelemente, Pagination und alle vier Post-Aktionsgruppen waren
vorhanden; eine Like-Aktion wechselte auf der isolierten Kopie erfolgreich den
Status. `php -l` war für alle sechs geänderten PHP-Dateien fehlerfrei. Die
Repository-Datei `Webseite - Codex/data/database.db` wurde nach der Prüfung
gegen den ursprünglichen SHA-256-Stand verifiziert.
