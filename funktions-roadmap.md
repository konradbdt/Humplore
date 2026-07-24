# Humplore Funktions-Roadmap

Stand: 2026-06-29

Diese Datei ist die zentrale Quelle fuer die naechsten Funktionsentscheidungen. Vor neuer Umsetzung zuerst diese Roadmap, die vorhandenen SpecKit-Dokumente unter `.specify/`, die OpenSpec-Dokumente unter `openspec/` und den aktuellen Codezustand abgleichen.

## Entscheidungsuebersicht

Diese Entscheidungen wurden am 2026-06-18 festgehalten und am 2026-06-29 strukturiert in die Roadmap einsortiert.

### Launch-Kriterien

- Admin-Moderations-Dashboard muss zum Launch vorhanden sein.
- Datenschutz, AGB, Impressum und Kontaktmoeglichkeit fuer Datenloeschung muessen zum Launch fertig sein.
- Alle MVP-Funktionen muessen mobil sauber funktionieren.
- Vor Freigabe muss eine vollstaendige Testphase durchlaufen werden.

### MVP-Scope

- Videos gehoeren ins MVP.
- Nicht verifizierte Creator duerfen nicht posten.
- Account-Loeschung gehoert ins MVP.
- Nutzer sollen anonyme Fragen stellen koennen.
- Creator sollen anonyme Beitraege erstellen koennen.
- Fragen, Kommentare und Beitraege koennen gemeldet werden.
- Fuer Posts reichen im MVP: Likes, Kommentare, Merken, Melden und Teilen.
- Profile sollen gefolgt und geteilt werden koennen.
- Creator-Profile sollen einen Button fuer Direktnachrichten haben.
- Creator sollen Kommentare deaktivieren koennen.
- Fragen muessen ueber Suche, Filter oder eine andere Strukturierung auffindbar sein.
- Es soll eine eigene Seite fuer gespeicherte Beitraege geben.

### Nicht im MVP oder spaeter

- Datenexport gehoert nicht ins MVP.
- Monetarisierung ist fuer den Launch irrelevant.
- Donation kommt spaeter.
- Profil-Meldungen gehoeren noch nicht ins MVP.
- Ausblendbarkeit von Ort, Sprache, Profilbild und aehnlichen Profilangaben kommt spaeter und ist kein Launch-Kriterium.

### Produkt- und Sichtbarkeitsregeln

- Wer verifiziert wird, darf Creator fuer ein bestimmtes Thema sein.
- Pflichtangaben im Profil: Username, Kategorie/Thema und Bio.
- Beruf wird nicht als eigenes Extrafeld wie Alter, Sprache oder Ort behandelt.
- Anonyme Creator-Beitraege erscheinen im Explore-Feed ohne sichtbare Creator-Zuordnung und ohne oeffentlichen Profil-Link.
- Creator sehen intern nicht, wer eine anonyme Frage gestellt hat.
- Nutzer-Anonymitaet soll in den Einstellungen aktivierbar sein; dann sieht niemand auf der Plattform, auch kein Creator, von wem die Frage oder der Beitrag stammt.
- Gegenueber den Betreibern besteht keine Anonymitaet; Betreiber koennen interne Zuordnungen sehen.
- Creator sollen in Suche und Discovery vorrangig priorisiert werden.

### Moderationsregeln

- Beim Melden soll ein Grund fuer Falschinformationen, falsche Empfehlungen oder problematische Empfehlungen ergaenzt werden.
- Zum Launch bearbeiten Abdul und der zweite Betreiber die Reports selbst.
- Gemeldete Inhalte bleiben zunaechst sichtbar und werden nicht automatisch temporaer ausgeblendet oder geloescht.
- Erst nach Bearbeitung des Reports wird entschieden, ob der Inhalt temporaer ausgeblendet, dauerhaft ausgeblendet oder geloescht wird.
- Je nach Schwere oder Wiederholung kann der Creator oder Ersteller des gemeldeten Inhalts verwarnt oder gesperrt werden.

### Offene Klaerungen

- Abdul klaert Vertrauen, Verifizierung, Verifizierungsseite und Creator-Modell weiter aus.
- Zu klaeren ist: welche Angaben abgefragt werden, was konkret geprueft wird und wer die Pruefung durchfuehrt.
- Budgetumfang wird bis zum naechsten Meeting geklaert.
- Vor dem Launch soll auf MySQL gewechselt werden; die endgueltige Datenbankentscheidung ist noch offen.
- Ausarbeitung der Testphase steht noch aus.
- Last-/Traffic-Verhalten muss technisch geklaert und getestet werden.
- Unklar transkribiert: "Waelder/Bilder werden nicht vor dem MVP auf der Datenbank ausgelagert." Diese Aussage muss fachlich bestaetigt werden, bevor daraus eine technische Aufgabe entsteht.

Pruefbasis dieser Fassung:

- Funktionsanalyse: `C:/Users/borns/Downloads/humplore_funktionsanalyse_bisher.adoc`
- SpecKit: `C:/Users/borns/OneDrive/Desktop/Humplore/Humplore/.specify/`
- OpenSpec: `C:/Users/borns/OneDrive/Desktop/Humplore/Humplore/openspec/`
- Code: `C:/Users/borns/OneDrive/Desktop/Humplore/Humplore/Webseite - Codex/`
- Syntaxpruefung ausgefuehrt fuer zentrale geaenderte PHP-Dateien: `search-discovery.php`, `platform-page.php`, `platform.php`, `search.php`, `save_post_handler.php`, `profile.php`

Statuswerte: `offen`, `vorbereitet`, `in Arbeit`, `implementiert`, `geprueft`

## Priorisierte Funktionen

### Themenbasierte Suche

- Funktion: Fehlertolerante themenbasierte Suche mit Suchvorschlaegen und verwandten Begriffen.
- Priorität: Sehr hoch
- Aktueller Stand laut Analyse: Suchleiste vorhanden; vorher nur exakte oder sehr passende Eingaben sinnvoll.
- Fehlende Funktionalität: Keine offene Kernfunktion aus der ersten Such-Spezifikation gefunden; Autocomplete als separate API bleibt ausserhalb des bisherigen Scopes.
- Nötige Änderung: Keine neue Umsetzung. Bei spaeteren Sucharbeiten Regressionen gegen direkte Treffer, Tippfehler, Vorschlaege und gefilterte Suche pruefen.
- Status: geprüft
- Bereits vorhandene SpecKit-Dokumente: `.specify/specs/001-search-discovery/spec.md`, `plan.md`, `tasks.md` mit abgeschlossenen Tasks.
- Bereits vorhandene OpenSpec-Dokumente: `openspec/changes/search-discovery-foundation/`, `openspec/specs/search/spec.md`; OpenSpec-Tasks abgeschlossen.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/app/support/search-discovery.php`, `Webseite - Codex/app/support/platform-page.php`, `Webseite - Codex/platform.php`, `Webseite - Codex/search.php`.
- Nächster konkreter Schritt: Als abgeschlossen behandeln; vor jeder neuen Discovery-Aenderung kurze Smoke-Pruefung fuer exakte Suche, Tippfehler-Suche und Suchvorschlaege wiederholen.

### Filterfunktion

- Funktion: Filter nach Themen/Themenkategorien, Kategorien und Sortierung.
- Priorität: Sehr hoch
- Aktueller Stand laut Analyse: Urspruenglich keine funktionalen Filter; Nutzer mussten Suchbegriffe kennen.
- Fehlende Funktionalität: Filter nach Beruf, Alter, Lebenssituation und Erfahrungstyp sind noch nicht als eigene Achsen umgesetzt; aktueller Scope deckt Themenkategorie, konkretes Thema per Legacy-URL und Beitrags-/Lebenskategorie ab.
- Nötige Änderung: Spaetere Filterachsen erst spezifizieren; bestehende Filterlogik nicht ohne Abgleich mit `topic_cat`, `topic` und `cat` erweitern.
- Status: geprüft
- Bereits vorhandene SpecKit-Dokumente: `.specify/specs/002-filter-discovery/`, `.specify/specs/005-topic-category-filter-model/`; Tasks abgeschlossen.
- Bereits vorhandene OpenSpec-Dokumente: `openspec/changes/filter-discovery-facets/`, `openspec/changes/topic-category-filter-model/`; Tasks abgeschlossen.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/app/support/platform-page.php`, `Webseite - Codex/app/support/search-discovery.php`, `Webseite - Codex/app/views/partials/profile-sidebar-category-link.php`, `Webseite - Codex/platform.php`.
- Nächster konkreter Schritt: Keine neue Filterachse ohne eigene Spec; falls Beruf/Alter/Lebenssituation priorisiert werden, zuerst Datenquelle und UI-Begriffe klaeren.

### Themen- / Kategorieuebersicht mit passenden Beitraegen

- Funktion: Browse-Uebersicht fuer Themenkategorien und Beitrags-/Lebenskategorien mit passenden Beitraegen und Creatorn.
- Priorität: Sehr hoch
- Aktueller Stand laut Analyse: Dummy-Kategorien waren sichtbar, aber nicht funktional.
- Fehlende Funktionalität: Fachliche Umsetzung ist im Code vorhanden; die OpenSpec-Task `4.4 Inspect desktop and mobile layout` ist noch offen. Eine dedizierte Themen-/Kategorieseite ist ebenfalls nicht Teil der ersten Umsetzung.
- Nötige Änderung: Desktop- und Mobile-Layout visuell pruefen; danach Tasks/Dokumente auf geprueften Stand bringen oder offene UI-Maengel erfassen.
- Status: implementiert
- Bereits vorhandene SpecKit-Dokumente: `.specify/specs/003-topic-category-overview/spec.md`, `plan.md`, `tasks.md`; fast abgeschlossen, Layout-Pruefung offen.
- Bereits vorhandene OpenSpec-Dokumente: `openspec/changes/topic-category-overview/`; Task `4.4` offen.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/app/support/platform-page.php`, `Webseite - Codex/platform.php`, `Webseite - Codex/css/styles.css`.
- Nächster konkreter Schritt: Nur visuelle Pruefung nachholen, keine neue Funktion ergaenzen.

### Creator-Profil

- Funktion: Creator-Profil mit Profilinformationen, Thema, Bio, Beitragen, Fragen und Follow-Aktion.
- Priorität: Sehr hoch
- Aktueller Stand laut Analyse: Creator-Profile sind vorhanden mit Profilbild, Username, Thema, Ort/Sprache, Bio, Follower-Anzahl, Beitragsanzahl, Folgen, Teilen, eigenen Beitraegen und Fragenbereich.
- Fehlende Funktionalität: Verifizierungsstatus, klar sichtbare Unterthemen/Tags als vertrauensbildende Struktur, ggf. staerkere Themenkategorie-Anbindung im Profil.
- Nötige Änderung: Eigene Spec fuer Profil-Vertrauen/Tags erstellen; bestehende Profilbasis nicht als vollstaendig verifiziertes Creator-Profil werten.
- Status: vorbereitet
- Bereits vorhandene SpecKit-Dokumente: Keine dedizierte Creator-Profil-Spec gefunden.
- Bereits vorhandene OpenSpec-Dokumente: Keine dedizierte Creator-Profil-OpenSpec gefunden.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/profile.php`, `Webseite - Codex/app/support/profile-page.php`, `Webseite - Codex/app/support/profile-actions.php`, `Webseite - Codex/app/views/partials/profile-header-card.php`, `Webseite - Codex/app/views/partials/profile-post-card.php`, `Webseite - Codex/app/views/partials/profile-questions-card.php`.
- Nächster konkreter Schritt: Spec fuer Profil-Tags und Vertrauenselemente anlegen, bevor am Profil weitergebaut wird.

### Verifizierung / Vertrauenselemente

- Funktion: Sichtbarer Verifizierungsstatus fuer Creator oder Erfahrungen.
- Priorität: Sehr hoch
- Aktueller Stand laut Analyse: Keine Verifizierung sichtbar.
- Fehlende Funktionalität: Badge, erklaerender Status, Prozess, Datenmodell und Posting-Sperre fuer nicht verifizierte Creator.
- Nötige Änderung: Verifizierungsmodell spezifizieren: was wird geprueft, wer darf pruefen, wo wird es angezeigt, wie wird Missbrauch verhindert und wie wird Posten fuer nicht verifizierte Creator blockiert.
- Status: offen
- Bereits vorhandene SpecKit-Dokumente: Keine.
- Bereits vorhandene OpenSpec-Dokumente: `openspec/changes/anonymous-questions/` fuer anonyme Fragen.
- Bereits geänderte oder relevante Code-Dateien: Keine belastbare Implementierung gefunden; spaeter relevant: `profile.php`, Profil-Partial, Postkarten.
- Nächster konkreter Schritt: OpenSpec/SpecKit fuer Verifizierungsstatus, Badge-Verhalten und Creator-Posting-Gate erstellen.

### Anonyme Nutzung / Privatsphaere

- Funktion: Anonym oder privat fragen/posten und Sichtbarkeit sensibler Profildaten steuern.
- Priorität: Hoch
- Aktueller Stand laut Analyse: Pseudonyme Nutzung durch frei waehlbaren Username moeglich; keine echte Anonymfunktion.
- Fehlende Funktionalität: Anonyme Fragen, anonyme Creator-Beitraege, Einstellungsoption fuer Nutzer-Anonymitaet, Profilbild/Ort/Sprache ausblenden, Sichtbarkeit einzelner Angaben.
- Nötige Änderung: Datenschutz- und Sichtbarkeitsmodell spezifizieren; interne Betreiber-Zuordnung bleibt erhalten, Creator und Besucher sehen bei anonymen Inhalten keine Identitaet.
- Status: offen
- Bereits vorhandene SpecKit-Dokumente: Keine.
- Bereits vorhandene OpenSpec-Dokumente: `openspec/changes/anonymous-questions/` fuer anonyme Fragen.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/app/support/profile-actions.php`, `Webseite - Codex/app/support/profile-page.php`, `Webseite - Codex/app/views/partials/profile-questions-card.php`, `Webseite - Codex/register.php`, `Webseite - Codex/update_profile.php`.
- Nächster konkreter Schritt: OpenSpec-Tasks fuer anonyme Fragen umsetzen; anonyme Beitraege und Profil-Sichtbarkeit separat spezifizieren.

### Fragefunktion an Creator

- Funktion: Nutzer stellen Fragen an Creator; Creator antworten direkt oder als Beitrag.
- Priorität: Hoch
- Aktueller Stand laut Analyse: Fragefunktion vorhanden; Fragen und Antworten werden angezeigt.
- Fehlende Funktionalität: Anonyme Fragestellung, Melden/Blockieren/Moderation/Freigabe, Schutz vor unangemessenen Inhalten.
- Nötige Änderung: Sicherheits- und Moderationsscope spezifizieren; bestehende Basisfunktion nicht als Schutzfunktion werten.
- Status: vorbereitet
- Bereits vorhandene SpecKit-Dokumente: Keine dedizierte Fragefunktion-Spec gefunden.
- Bereits vorhandene OpenSpec-Dokumente: `openspec/changes/anonymous-questions/` fuer anonyme Fragen.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/app/support/profile-actions.php`, `Webseite - Codex/app/support/profile-page.php`, `Webseite - Codex/app/views/partials/profile-questions-card.php`, `Webseite - Codex/profile.php`.
- Nächster konkreter Schritt: OpenSpec-Tasks fuer anonyme Fragen umsetzen; Schutz-/Meldefunktionen danach separat spezifizieren.

### Favoriten / Speichern

- Funktion: Beitraege oder Creator merken und spaeter wiederfinden.
- Priorität: Hoch
- Aktueller Stand laut Analyse: Urspruenglich keine Speicher- oder Favoritenfunktion.
- Fehlende Funktionalität: Code fuer `Merken` von Beitraegen existiert, aber SpecKit-Tasks fuer Post-Aktionsbuttons sind nicht abgehakt; eigene Seite fuer gespeicherte Beitraege, Creator speichern und Sammlungen fehlen.
- Nötige Änderung: Bestehendes `Merken` verifizieren und Tasklisten aktualisieren; danach separate Spec fuer gespeicherte Beitraege als eigene Seite und spaeter Creator-Speichern.
- Status: in Arbeit
- Bereits vorhandene SpecKit-Dokumente: `.specify/specs/004-post-action-buttons/`; Tasks noch offen.
- Bereits vorhandene OpenSpec-Dokumente: `openspec/changes/post-action-buttons/`; beschreibt `Merken`, gespeicherte Posts und Toolbar.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/save_post_handler.php`, `Webseite - Codex/app/support/helpers.php`, `Webseite - Codex/app/support/content.php`, `Webseite - Codex/app/views/partials/platform-post-card.php`, `Webseite - Codex/app/views/partials/profile-post-card.php`, `Webseite - Codex/platform.php`, `Webseite - Codex/profile.php`, `Webseite - Codex/create_tables.sql`.
- Nächster konkreter Schritt: Vor Umsetzung der gespeicherten-Beitraege-Seite zuerst vorhandene `Merken`-Umsetzung pruefen und Taskstatus korrigieren.

### Folgen von Creatorn oder Themen

- Funktion: Creatorn und spaeter Themen folgen, mit Feed-/Benachrichtigungswirkung.
- Priorität: Mittel bis hoch
- Aktueller Stand laut Analyse: Creator-Follow vorhanden; Themen folgen, Benachrichtigungen und Uebersicht gefolgter Creator fehlen.
- Fehlende Funktionalität: Themen folgen, Benachrichtigungen, Profiluebersicht gefolgter Creator; Feed-Personalisierung ist nur teilweise ueber Following-Mode vorhanden.
- Nötige Änderung: Follow-Modell erweitern, aber erst klaeren, ob Thema als Themenkategorie oder konkretes Thema gefolgt wird.
- Status: vorbereitet
- Bereits vorhandene SpecKit-Dokumente: Keine dedizierte Follow-Spec gefunden.
- Bereits vorhandene OpenSpec-Dokumente: Keine dedizierte Follow-OpenSpec gefunden.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/app/support/profile-actions.php`, `Webseite - Codex/app/support/profile-page.php`, `Webseite - Codex/app/views/partials/profile-header-card.php`, `Webseite - Codex/app/support/platform-page.php`, `Webseite - Codex/platform.php`.
- Nächster konkreter Schritt: Spec fuer Themen-Follow und Benachrichtigungen anlegen; bestehende Creator-Follow-Logik dabei erhalten.

### Kommentare / Austausch unter Beitraegen

- Funktion: Kommentare und Austausch unter Beitraegen.
- Priorität: Hoch
- Aktueller Stand laut Analyse: Kommentare vorhanden; keine Antworten auf Kommentare, keine Meldefunktion, keine Likes/Reaktionen auf Kommentare, keine Moderation/Deaktivierung.
- Fehlende Funktionalität: Threaded replies, Kommentar-Melden, Kommentar-Likes, Creator-Moderation, Kommentare deaktivieren.
- Nötige Änderung: Moderations- und Kommentar-Deaktivierungsmodell spezifizieren; bestehende einfache Kommentarform nicht erweitern ohne Sicherheitsentscheidung.
- Status: vorbereitet
- Bereits vorhandene SpecKit-Dokumente: Keine dedizierte Kommentar-Spec gefunden.
- Bereits vorhandene OpenSpec-Dokumente: Keine dedizierte Kommentar-OpenSpec gefunden.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/app/support/content.php`, `Webseite - Codex/app/support/profile-actions.php`, `Webseite - Codex/app/views/partials/platform-post-card.php`, `Webseite - Codex/app/views/partials/profile-post-card.php`, `Webseite - Codex/platform.php`, `Webseite - Codex/profile.php`.
- Nächster konkreter Schritt: OpenSpec fuer Kommentar-Schutzfunktionen inklusive Kommentar-Melden und Kommentar-Deaktivierung erstellen.

### Likes / hilfreiche Bewertung

- Funktion: Reaktion auf Beitraege, ggf. hilfreiche Bewertung.
- Priorität: Mittel
- Aktueller Stand laut Analyse: Beitraege koennen geliked werden; keine speziellere Hilfreich-Bewertung.
- Fehlende Funktionalität: Eine echte Hilfreich-/Danke-/Nachvollziehbar-Bewertung und Sortierung nach hilfreichsten Beitraegen fehlen. Die vorhandene Reaktion wurde im Code sichtbar zu `Neues gelernt!` umbenannt, aber die Post-Aktionsbutton-Tasks sind noch nicht abgeschlossen.
- Nötige Änderung: Entscheidung treffen, ob `Neues gelernt!` bereits die Hilfreich-Bewertung ersetzt oder ob ein separates Bewertungssystem benoetigt wird.
- Status: in Arbeit
- Bereits vorhandene SpecKit-Dokumente: `.specify/specs/004-post-action-buttons/`; Tasks offen.
- Bereits vorhandene OpenSpec-Dokumente: `openspec/changes/post-action-buttons/`.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/like_handler.php`, `Webseite - Codex/app/views/partials/platform-post-card.php`, `Webseite - Codex/app/views/partials/profile-post-card.php`, `Webseite - Codex/platform.php`, `Webseite - Codex/profile.php`.
- Nächster konkreter Schritt: Post-Aktionsbutton-Umsetzung pruefen und dann entscheiden, ob weitere Reaktionsarten spezifiziert werden.

## Zurueckgestellt / niedrig priorisiert

### Personalisierter Feed

- Funktion: Feed auf Basis gefolgter Creator, Themen, gespeicherter Beitraege oder Interessen.
- Priorität: Niedrig
- Aktueller Stand laut Analyse: Nicht zentral fuer erste Version; Folgen veraendert den Feed laut Analyse nicht.
- Fehlende Funktionalität: Vollstaendige Personalisierung, Ranking nach Interessen, Themen-Follow, gespeicherte Inhalte als Signal.
- Nötige Änderung: Erst nach stabilen Follow-/Speichern-/Themenmodellen spezifizieren.
- Status: vorbereitet
- Bereits vorhandene SpecKit-Dokumente: Keine.
- Bereits vorhandene OpenSpec-Dokumente: Keine.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/app/support/platform-page.php`, `Webseite - Codex/platform.php`; Following-Mode nutzt vorhandene `Follows`.
- Nächster konkreter Schritt: Vorerst zurueckstellen; nicht vor Follow-/Themen-Spec beginnen.

## Noch nicht detailliert analysierte Funktionen

### Reporting / Meldefunktion fuer Beitraege, Fragen und Kommentare

- Funktion: Sichtbare Inhalte melden und durch Betreiber bearbeiten lassen.
- Priorität: Sehr hoch
- Aktueller Stand laut Analyse: Teilweise vorbereitet; es gibt OpenSpec-Unterlagen fuer Fragen/Kommentare, aber der neue Scope umfasst auch Beitraege.
- Fehlende Funktionalität: Melde-UI fuer Beitraege, Meldegrund fuer Falschinformationen/falsche Empfehlungen, Persistenz, Review-Status und Betreiberbearbeitung.
- Nötige Änderung: Reporting-Spec auf Fragen, Kommentare und Beitraege erweitern; Profile bewusst aus dem MVP ausschliessen.
- Status: offen
- Bereits vorhandene SpecKit-Dokumente: Keine.
- Bereits vorhandene OpenSpec-Dokumente: `openspec/changes/question-comment-reporting/` fuer Fragen und Kommentare.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/report_handler.php`, `Webseite - Codex/app/support/reports.php`, Report-Partial und Frage-/Kommentar-Renderings.
- Nächster konkreter Schritt: Bestehenden Reporting-Scope pruefen, Post-Reporting und neue Meldegruende spezifizieren.

### Moderationssystem / Admin-Dashboard

- Funktion: Betreiber bearbeiten Reports und entscheiden ueber Sichtbarkeit, Loeschung, Verwarnung oder Sperrung.
- Priorität: Sehr hoch
- Aktueller Stand laut Analyse: Noch offen fuer weitere Analyse.
- Fehlende Funktionalität: Admin-Moderations-Dashboard, Rollen, Queue, Status, Entscheidungen, Audit-Trail, Verwarnungs-/Sperrlogik.
- Nötige Änderung: Moderationskonzept vor Datenmodell festlegen; Inhalte bleiben nach Meldung sichtbar, bis ein Betreiber entscheidet.
- Status: offen
- Bereits vorhandene SpecKit-Dokumente: Keine.
- Bereits vorhandene OpenSpec-Dokumente: Keine.
- Bereits geänderte oder relevante Code-Dateien: Noch keine belastbare Implementierung gefunden.
- Nächster konkreter Schritt: OpenSpec fuer Admin-Moderations-Dashboard als Launch-Kriterium anlegen.

### Datenschutz / DSGVO-Hinweise

- Funktion: Datenschutzinformationen, Rechtstexte, Datenloeschkontakt und Transparenzhinweise.
- Priorität: Sehr hoch
- Aktueller Stand laut Analyse: Noch offen fuer weitere Analyse.
- Fehlende Funktionalität: Belastbare Datenschutzseite, Datenloeschkontakt, DSGVO-Hinweise fuer sensible Inhalte, Loeschkonzept; Datenexport ist nicht MVP.
- Nötige Änderung: Rechtliche Anforderungen klaeren und dann UI/Content/Account-Funktionen spezifizieren.
- Status: offen
- Bereits vorhandene SpecKit-Dokumente: Keine.
- Bereits vorhandene OpenSpec-Dokumente: Keine.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/agbs.html`, `Webseite - Codex/impressum.html`, `Webseite - Codex/index.html`; ein Link auf `datenschutz.html` ist sichtbar, aber keine passende Datei wurde in der Dateiliste gefunden.
- Nächster konkreter Schritt: Datenschutzseite, AGB, Impressum und Kontakt fuer Datenloeschung als Launch-Paket planen.

### Registrierung / Login / Account-Loeschung

- Funktion: Nutzerregistrierung, Login, Logout und Account-Loeschung.
- Priorität: Hoch
- Aktueller Stand laut Analyse: Authentifizierung ist implementiert; Account-Loeschung ist noch nicht belastbar geprueft.
- Fehlende Funktionalität: Nicht geprueft: Passwortregeln, Fehlertexte, Session-Haertung, Account-Loeschung, E-Mail-Verifizierung.
- Nötige Änderung: Auth-Bestand gesondert pruefen und Account-Loeschung als MVP-Anforderung spezifizieren.
- Status: vorbereitet
- Bereits vorhandene SpecKit-Dokumente: Keine.
- Bereits vorhandene OpenSpec-Dokumente: Keine.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/login.php`, `Webseite - Codex/register.php`, `Webseite - Codex/process_login.php`, `Webseite - Codex/process_register.php`, `Webseite - Codex/logout.php`, `Webseite - Codex/app/support/auth.php`.
- Nächster konkreter Schritt: Security-/Auth-Review inklusive Account-Loeschung durchfuehren.

### Creator-Qualifizierung

- Funktion: Qualifizierung oder Pruefung von Creatorn vor/waehrend Nutzung.
- Priorität: Sehr hoch
- Aktueller Stand laut Analyse: Noch offen fuer weitere Analyse.
- Fehlende Funktionalität: Kriterien, Prozess, Status, Admin-Entscheidung, Anzeige im Profil und Sperre fuer nicht verifizierte Creator.
- Nötige Änderung: Zusammen mit Verifizierung und Vertrauenselementen spezifizieren.
- Status: offen
- Bereits vorhandene SpecKit-Dokumente: Keine.
- Bereits vorhandene OpenSpec-Dokumente: Keine.
- Bereits geänderte oder relevante Code-Dateien: Noch keine belastbare Implementierung gefunden.
- Nächster konkreter Schritt: Mit Verifizierungs-Spec zusammenfassen, sofern Abdul das Modell nicht bewusst trennt.

### Direktnachrichten an Creator

- Funktion: Nutzer koennen einem Creator direkt vom Creator-Profil aus schreiben.
- Priorität: Hoch
- Aktueller Stand laut Analyse: Noch offen fuer weitere Analyse.
- Fehlende Funktionalität: Profil-Button, Nachrichtenmodell, Inbox/Thread-Ansicht, Missbrauchsschutz, Blockieren/Melden, Benachrichtigungen.
- Nötige Änderung: Scope klaeren: MVP nur Button/Start eines Threads oder vollstaendige private Nachrichtenfunktion.
- Status: offen
- Bereits vorhandene SpecKit-Dokumente: Keine.
- Bereits vorhandene OpenSpec-Dokumente: Keine.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/profile.php`, `Webseite - Codex/app/views/partials/profile-header-card.php`, spaeter eigene Message-Handler/Tabellen.
- Nächster konkreter Schritt: OpenSpec fuer Direktnachrichten erstellen, bevor UI oder Datenmodell gebaut wird.

### Video- / Vlog-Inhalte

- Funktion: Video- oder Vlog-Beitraege.
- Priorität: Hoch
- Aktueller Stand laut Analyse: Videos sind als MVP-Entscheidung festgehalten; technische Umsetzung ist noch nicht abschliessend geprueft.
- Fehlende Funktionalität: Upload, Transcoding, Preview, Limits, Moderation, mobile Wiedergabe und Speicherstrategie.
- Nötige Änderung: Media-Scope pruefen und Video-Anforderungen spezifizieren.
- Status: vorbereitet
- Bereits vorhandene SpecKit-Dokumente: Keine.
- Bereits vorhandene OpenSpec-Dokumente: Keine.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/media.php`, `Webseite - Codex/create_post.php`, `Webseite - Codex/posten.php`, `Webseite - Codex/process_post.php`, `Webseite - Codex/app/support/post-editor.php`.
- Nächster konkreter Schritt: Bestehende Medienfunktion analysieren, bevor Video als Feature geplant wird.

### Mobile Optimierung

- Funktion: Mobile Darstellung und Bedienbarkeit der Plattform.
- Priorität: Sehr hoch
- Aktueller Stand laut Analyse: Noch offen fuer weitere Analyse.
- Fehlende Funktionalität: Systematische Mobile-Pruefung ueber Suche, Explore, Profil, Fragen, Post-Aktionen, Moderation und Browse-Uebersicht.
- Nötige Änderung: Mobile QA-Checkliste und Breakpoints definieren; offene Layout-Pruefung der Browse-Uebersicht nachholen.
- Status: offen
- Bereits vorhandene SpecKit-Dokumente: Teilweise beruehrt durch `.specify/specs/003-topic-category-overview/` und `.specify/specs/004-post-action-buttons/`, aber keine dedizierte Mobile-Spec.
- Bereits vorhandene OpenSpec-Dokumente: Teilweise beruehrt durch `openspec/changes/topic-category-overview/` und `openspec/changes/post-action-buttons/`.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/platform.php`, `Webseite - Codex/profile.php`, `Webseite - Codex/css/styles.css`.
- Nächster konkreter Schritt: Mobile QA als Launch-Kriterium ausarbeiten.

### Ladezeit / Performance / Last

- Funktion: Performance, Ladezeit, Skalierbarkeit und Verhalten bei hohem Verkehr.
- Priorität: Hoch
- Aktueller Stand laut Analyse: Noch offen fuer weitere Analyse.
- Fehlende Funktionalität: Messwerte, Query-Profiling, Asset-Optimierung, Pagination-/Feed-Performance unter Datenlast, Lasttestkonzept.
- Nötige Änderung: Performance-Baseline, kritische Seiten und Lasttest-Szenarien festlegen.
- Status: offen
- Bereits vorhandene SpecKit-Dokumente: Keine dedizierte Performance-Spec.
- Bereits vorhandene OpenSpec-Dokumente: Keine dedizierte Performance-OpenSpec.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/platform.php`, `Webseite - Codex/profile.php`, `Webseite - Codex/app/support/search-discovery.php`, `Webseite - Codex/app/support/platform-page.php`.
- Nächster konkreter Schritt: Metriken definieren und einfache lokale Messung fuer Explore/Search/Profile plus Lasttestplanung erstellen.

### Datenbankwechsel auf MySQL

- Funktion: Persistenz vor Launch von der aktuellen lokalen Datenbank auf MySQL umstellen.
- Priorität: Hoch
- Aktueller Stand laut Analyse: Entscheidung noch offen; MySQL-Wechsel ist angedacht, aber nicht final bewertet.
- Fehlende Funktionalität: Migrationsplan, Schema-Kompatibilitaet, Hosting-Konfiguration, Backup-/Restore-Plan.
- Nötige Änderung: Aktuelle SQLite-/Dateidatenbank-Nutzung pruefen und MySQL-Migrationsrisiken bewerten.
- Status: offen
- Bereits vorhandene SpecKit-Dokumente: Keine.
- Bereits vorhandene OpenSpec-Dokumente: Keine.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/config/database.php`, `Webseite - Codex/create_tables.sql`, `Webseite - Codex/data/database.db`.
- Nächster konkreter Schritt: Technische Entscheidungsnotiz fuer MySQL vs. aktueller Datenbankstand erstellen.

### Monetarisierung / Werbung / Unterstuetzungsfunktion

- Funktion: Monetarisierung, Werbung, bezahlte Inhalte oder Creator-Unterstuetzung.
- Priorität: Niedrig
- Aktueller Stand laut Analyse: Fuer Launch irrelevant; Donation ist als spaetere Post-Aktion dokumentiert.
- Fehlende Funktionalität: Payment-, Payout-, Refund-, Donation- oder Werbelogik.
- Nötige Änderung: Monetarisierungsmodell spaeter getrennt spezifizieren; Datenschutz, Moderation und Vertrauen beruecksichtigen.
- Status: vorbereitet
- Bereits vorhandene SpecKit-Dokumente: `.specify/specs/004-post-action-buttons/` nennt Donation explizit als out of scope/spaeter.
- Bereits vorhandene OpenSpec-Dokumente: `openspec/changes/post-action-buttons/` dokumentiert spaetere post-level Donation als nicht umgesetzt.
- Bereits geänderte oder relevante Code-Dateien: `Webseite - Codex/posten.php`, `Webseite - Codex/process_post.php`, `Webseite - Codex/app/support/post-editor.php`, `Webseite - Codex/create_tables.sql`; `price_cents`/paid-post-Anzeichen vorhanden, aber keine fertige Unterstuetzungsfunktion.
- Nächster konkreter Schritt: Nicht mit Post-Aktionsbutton-Scope vermischen; nach Launch eigene Monetarisierungs-Spec erstellen.


