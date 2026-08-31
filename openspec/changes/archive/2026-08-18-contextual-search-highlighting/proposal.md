# Proposal: Contextual Search Highlighting

## Why

Humplore findet bereits Profile und Beiträge über exakte sowie verwandte
Suchbegriffe. Nutzende können in den Ergebnissen aber nicht schnell erkennen,
welche Wörter den Treffer ausgelöst haben. Gleichzeitig bleibt der Bereich
`Gestellte Fragen` im Explore-Layout von der aktiven Suche unabhängig und kann
dadurch unpassende Fragen anzeigen.

## What Changes

- Wörtlich passende Suchbegriffe werden in sichtbaren Profil-, Beitrags- und
  Fragentexten gelb hervorgehoben.
- Treffer, die nur über einen verwandten Begriff gefunden wurden, werden als
  `Thematisch verwandt` gekennzeichnet; hervorgehoben wird der tatsächlich im
  Text vorkommende verwandte Begriff.
- Fragen werden bei aktiver Suche thematisch gefiltert.
- Die rechte Fragenleiste heißt bei aktiver Suche `Passende Fragen` und zeigt
  ausschließlich passende Fragen.
- Gibt es keine passende Frage, zeigt die Fragenleiste einen klaren
  Leerzustand statt zufälliger oder unpassender Fragen.
- Ohne aktive Suche bleibt das bisherige Verhalten der Fragenleiste erhalten.
- Die bestehende leichte PHP-/SQLite-Suche bleibt bestehen; eine vollständige
  semantische Suchmaschine ist nicht Teil dieser Änderung.

## Capabilities

### Modified Capabilities

- **search** - nachvollziehbare Suchtreffer mit Hervorhebung und kontextbezogenen Fragen.

## Impact

- Betrifft die gemeinsame Suchlogik unter
  `Webseite - Codex/app/support/search-discovery.php`.
- Betrifft die Suchdarstellung in `Webseite - Codex/search.php` und
  `Webseite - Codex/platform.php`.
- Betrifft das Laden und Darstellen der Fragen im Explore-Bereich.
- Erfordert sichere HTML-Ausgabe, damit die Hervorhebung kein ungefiltertes
  Such- oder Inhalts-HTML einschleust.

## Rollback

Die Hervorhebung und Suchkennzeichnung entfernen und die Fragenleiste wieder
unabhängig von der Suchanfrage laden. Die bestehende Profil- und Beitragssuche
bleibt dabei unverändert erhalten.
