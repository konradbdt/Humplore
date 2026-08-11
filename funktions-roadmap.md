# Humplore Funktions-Roadmap

Stand: 11. August 2026

Diese Datei enthält nur Prioritäten und Reihenfolge. Verbindliche
Produktentscheidungen stehen in [`docs/produktumfang.md`](docs/produktumfang.md),
der aktuelle Umsetzungsstand in
[`docs/projektstatus.md`](docs/projektstatus.md). Detaillierte Anforderungen und
Aufgaben werden ausschließlich in OpenSpec geführt.

## P0: Arbeitsgrundlage sichern

### 1. Aktiven Codebaum konsolidieren

**Ziel:** Nur eine bearbeitbare Anwendung und eine nachvollziehbare Historie.

- `Webseite - Codex/` als einzigen aktiven Codebaum beibehalten.
- Den verworfenen Baum `Webseite - Redesign/` erst nach einer gesonderten
  Sicherungsentscheidung archivieren oder entfernen.
- Änderungen im Codex-Baum nachvollziehbar committen.

**Abgeschlossen, wenn:** `Webseite - Codex/` als einziger aktiver Codebaum
dokumentiert und versioniert ist.

## P0: Launch-Blocker

### 2. Creator-Verifizierung und Posting-Gate

**Nächster Schritt:** OpenSpec-Change für Nachweise, Rollen, Statusübergänge,
Badge, Adminentscheidung und Posting-Sperre anlegen. Vorher müssen die offenen
Produktfragen in `docs/produktumfang.md` entschieden werden.

### 3. Moderationsdashboard und Post-Reporting

**Abhängigkeit:** Reporting für Fragen und Kommentare ist bereits abgeschlossen.

**Nächster Schritt:** Einen gemeinsamen OpenSpec-Change für Post-Reporting,
Moderationsqueue, Entscheidungen, Audit-Trail, Verwarnung und Sperre erstellen.
Profile bleiben außerhalb des MVP.

### 4. Datenschutz und Account-Löschung

**Nächster Schritt:** Datenschutzseite, Löschkontakt, Datenlöschablauf und
Account-Löschung als zusammenhängendes Launch-Paket spezifizieren. AGB,
Impressum und Kontaktseite sind vorhanden, ersetzen aber keine
Datenschutzdokumentation.

### 5. Anonyme Fragen abschließen

**Aktiver Change:** `openspec/changes/anonymous-questions/`

**Nächster Schritt:** Die vorhandenen 25 Tasks implementieren und prüfen.
Interne Account-Zuordnung bleibt bestehen; Creator und Besucher sehen keine
Identität.

### 6. Anonyme Beiträge und Anonymitätseinstellung

**Abhängigkeit:** Erst nach anonymen Fragen.

**Nächster Schritt:** Separaten OpenSpec-Change für Creator-Beiträge,
öffentliche Entkopplung vom Profil und die Nutzer-Anonymitätseinstellung
erstellen.

### 7. Gespeicherte Beiträge anzeigen

**Abgeschlossen:** Die gemeinsame Post-Aktionsleiste ist in Explore,
eigenständiger Suche, Profil und Modal geprüft. Der Change ist unter
`openspec/changes/archive/2026-08-11-post-action-buttons/` archiviert.

**Nächster Schritt:** Einen eigenen kleinen OpenSpec-Change für die Seite
„Gemerkte Beiträge“ erstellen. Donation bleibt ein separates späteres Thema.

### 8. Themen-/Kategorieübersicht abnehmen

**Aktiver Change:** `openspec/changes/topic-category-overview/`

**Nächster Schritt:** Die einzige offene Aufgabe ist die manuelle
Desktop-/Mobile-Prüfung. Bei Erfolg Change archivieren; bei Fehlern konkrete
Korrekturtasks ergänzen.

### 9. Mobile und funktionale MVP-Abnahme

**Nächster Schritt:** Die MVP-Abnahmematrix aus
[`docs/teststrategie.md`](docs/teststrategie.md) ausführen und um
featurebezogene OpenSpec-Prüfungen ergänzen.

## P1: Weitere bestätigte MVP-Funktionen

### 10. Direktnachrichten

Vor der Umsetzung Produktumfang für Threadstart, Inbox, Benachrichtigungen,
Blockieren und Melden entscheiden und anschließend als eigenen Change
spezifizieren.

### 11. Kommentarsteuerung

Kommentar-Deaktivierung durch Creator sowie Kommentar-Melden und Moderation
gemeinsam spezifizieren. Threaded Replies und Kommentar-Likes sind nicht
automatisch Teil dieses Changes.

### 12. Medien-Upload reparieren und Video unterstützen

Die aktuelle Posting-Seite unterstützt nur Bilder; deren Darstellung in Feed und
Suche ist für nicht-JPEG-Dateien unzuverlässig. Zuerst Bild-Upload und
Bildanzeige end-to-end prüfen und vereinheitlichen. Danach Video als eigenen
Umfang mit Formaten, Limits, Speicherung, Vorschau, Transcoding, Moderation
und mobiler Wiedergabe spezifizieren.

### 13. Performance, Last und Datenbank

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
2. Verifizierung und Moderation spezifizieren.
3. Datenschutz und Account-Löschung spezifizieren.
4. Bereits aktive MVP-Changes abschließen.
5. Mobile Gesamtprüfung durchführen.
6. Erst danach P1- und P2-Funktionen beginnen.
