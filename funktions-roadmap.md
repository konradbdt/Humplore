# Humplore Funktions-Roadmap

Stand: 12. August 2026

Diese Datei enthält nur Prioritäten und Reihenfolge. Verbindliche
Produktentscheidungen stehen in [`docs/produktumfang.md`](docs/produktumfang.md),
der aktuelle Umsetzungsstand in
[`docs/projektstatus.md`](docs/projektstatus.md). Detaillierte Anforderungen und
Aufgaben werden ausschließlich in OpenSpec geführt.

## P0: Arbeitsgrundlage sichern

### 1. Aktiven Codebaum konsolidieren

**Ziel:** Nur eine bearbeitbare Anwendung und eine nachvollziehbare Historie.

- `Webseite - Codex/` als einzigen aktiven Codebaum beibehalten.
- Der verworfene Baum `Webseite - Redesign/` ist nicht mehr vorhanden und darf
  nicht erneut als Arbeitsgrundlage angenommen werden.
- Änderungen im Codex-Baum nachvollziehbar committen.

**Abgeschlossen, wenn:** `Webseite - Codex/` als einziger aktiver Codebaum
dokumentiert und versioniert ist.

## P0: Launch-Blocker

### 2. Moderationsdashboard und Post-Reporting

**Abgeschlossen:** Anonyme Fragen sind implementiert, vollständig geprüft und
unter `openspec/changes/archive/2026-08-12-anonymous-questions/` archiviert.
Anonyme Beiträge sind nicht vorgesehen; Creator können stattdessen unter
einem frei gewählten, auch fiktiven Nutzernamen veröffentlichen.

**Ausgangslage:** Reporting für Fragen und Kommentare ist bereits abgeschlossen.

**Nächster Schritt:** Einen gemeinsamen OpenSpec-Change für Post-Reporting,
Moderationsqueue, Entscheidungen, Audit-Trail, Verwarnung und Sperre erstellen.
Profile bleiben außerhalb des MVP.

### 3. Datenschutz und Account-Löschung

**Nächster Schritt:** Datenschutzseite, Löschkontakt, Datenlöschablauf und
Account-Löschung als zusammenhängendes Launch-Paket spezifizieren. AGB,
Impressum und Kontaktseite sind vorhanden, ersetzen aber keine
Datenschutzdokumentation.

### 4. Geschützten Zugang vollständig abnehmen

**Produktregel:** Ohne Anmeldung sind nur Einstieg, Registrierung, Login,
Rechtstexte und Kontakt erreichbar. Alle eigentlichen Humplore-Inhalte und
-Funktionen setzen Anmeldung voraus.

**Nächster Schritt:** Alle öffentlichen Routen und zustandsändernden Endpunkte
systematisch prüfen, direkte Gastaufrufe serverseitig absichern und die
erwarteten Login-Weiterleitungen beziehungsweise Fehlerantworten dokumentieren.

### 5. Gespeicherte Beiträge anzeigen

**Abgeschlossen:** Die gemeinsame Post-Aktionsleiste ist in Explore,
eigenständiger Suche, Profil und Modal geprüft. Der Change ist unter
`openspec/changes/archive/2026-08-11-post-action-buttons/` archiviert.

**Nächster Schritt:** Einen eigenen kleinen OpenSpec-Change für die Seite
„Gemerkte Beiträge“ erstellen. Donation bleibt ein separates späteres Thema.

### 6. Themen-/Kategorieübersicht abnehmen

**Aktiver Change:** `openspec/changes/topic-category-overview/`

**Nächster Schritt:** Die einzige offene Aufgabe ist die manuelle
Desktop-/Mobile-Prüfung. Bei Erfolg Change archivieren; bei Fehlern konkrete
Korrekturtasks ergänzen.

### 7. Mobile und funktionale MVP-Abnahme

**Nächster Schritt:** Die MVP-Abnahmematrix aus
[`docs/teststrategie.md`](docs/teststrategie.md) ausführen und um
featurebezogene OpenSpec-Prüfungen ergänzen.

## P1: Weitere bestätigte MVP-Funktionen

### 8. Creator-Verifizierung und bevorzugte Ausspielung

Die Verifizierung ist optional und darf das Veröffentlichen nicht sperren.
Vor der Umsetzung sind Nachweise, Rollen, Statusübergänge, Badge sowie
Kriterien und Stärke des Ranking-Vorteils zu entscheiden. Danach einen eigenen
OpenSpec-Change für Betreiberprüfung, Vertrauenssignal und bevorzugte
Ausspielung in Suche und Discovery erstellen.

### 9. Direktnachrichten

Vor der Umsetzung Produktumfang für Threadstart, Inbox, Benachrichtigungen,
Blockieren und Melden entscheiden und anschließend als eigenen Change
spezifizieren.

### 10. Kommentarsteuerung

Kommentar-Deaktivierung durch Creator sowie Kommentar-Melden und Moderation
gemeinsam spezifizieren. Threaded Replies und Kommentar-Likes sind nicht
automatisch Teil dieses Changes.

### 11. Medien-Upload reparieren und Video unterstützen

Die aktuelle Posting-Seite unterstützt nur Bilder; deren Darstellung in Feed und
Suche ist für nicht-JPEG-Dateien unzuverlässig. Zuerst Bild-Upload und
Bildanzeige end-to-end prüfen und vereinheitlichen. Danach Video als eigenen
Umfang mit Formaten, Limits, Speicherung, Vorschau, Transcoding, Moderation
und mobiler Wiedergabe spezifizieren.

### 12. Performance, Last und Datenbank

- Messbare Baseline für Explore, Suche und Profile erfassen.
- Den historischen 30-Sekunden-Timeout reproduzieren oder ausschließen.
- Lasttests und Zielwerte definieren.
- SQLite-vs.-MySQL-Entscheidung dokumentieren.
- Migration, Backup und Restore planen.

## P2: Nach MVP

- Profilfilter Stufe 2 mit normalisierten Sprachen und sensiblen Attributen;
  der aktive Change bleibt bis zur erneuten Priorisierung pausiert.
- Themen-Follow und Benachrichtigungen.
- Übersicht gefolgter Creator.
- Vollständige Feed-Personalisierung.
- Datenexport.
- Monetarisierung und postbezogene Donation.
- Profilmeldungen.
- Echtzeitfunktionen, PWA, Analytics und native App.

## Reihenfolge für neue Arbeit

1. Codex-Codebaum und Git-Stand sichern.
2. Moderation sowie Datenschutz und Account-Löschung spezifizieren.
3. Loginpflicht für geschützte Inhalte und Funktionen vollständig abnehmen.
4. Weitere aktive MVP-Changes abschließen.
5. Mobile Gesamtprüfung durchführen.
6. Erst danach optionale Verifizierung und weitere P1-/P2-Funktionen beginnen.
