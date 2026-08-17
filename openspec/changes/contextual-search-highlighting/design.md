# Design: Contextual Search Highlighting

## Current State

Die gemeinsame Suchlogik liefert Profile und Beiträge anhand von Suchbegriff,
verwandten Begriffen und Vorschlägen. Die sichtbaren Treffertexte enthalten
keine Suchmarkierung. Fragen werden separat geladen und in der rechten
Explore-Leiste sowie im Fragenmodal unabhängig von der aktuellen Suche
angezeigt.

## Target State

Eine aktive Suche erklärt visuell, warum Inhalte erscheinen. Wörtliche
Treffer werden gelb markiert. Bei verwandten Treffern wird nicht so getan, als
stünde der eingegebene Begriff im Text: Stattdessen wird der tatsächlich
passende Begriff hervorgehoben und der Treffer als `Thematisch verwandt`
gekennzeichnet.

Die Fragenleiste reagiert auf dieselbe Suchanfrage. Sie zeigt nur passende
Fragen und trägt bei aktiver Suche den Titel `Passende Fragen`. Ohne Treffer
erscheint ein eindeutiger Leerzustand. Ohne aktive Suche bleibt die bisherige
allgemeine Fragenansicht bestehen.

## Matching Decisions

### Profiles and posts

Die bestehende Suche bleibt maßgeblich. Durchsucht werden weiterhin
Creator-Profile und -Themen sowie Beitragstitel, Beitragstext und
Beitragskategorien. Hervorhebungen ändern weder Treffermenge noch Ranking.

### Questions

Fragen werden mindestens über den Fragetext und das Hauptthema des
adressierten Creators abgeglichen. Dadurch kann eine Suche nach `Krebs` auch
eine Frage finden, deren Fragetext den Begriff nicht enthält, die aber an
einen Creator mit dem Hauptthema Krebs gerichtet ist.

Verwandte Begriffe dürfen aus derselben begrenzten Begriffsermittlung wie die
Profil- und Beitragssuche stammen. Eine automatisch vollständige medizinische
Synonym- oder Wissensbasis wird nicht zugesichert. Begriffe wie `Onkologie`
oder konkrete Krebsarten gelten nur dann als verwandt, wenn die vorhandene
Suchlogik oder eine später gepflegte Zuordnung diese Beziehung kennt.

## Highlighting Decisions

- Inhalte und Suchbegriffe werden zuerst als Text sicher escaped.
- Nur sichtbare, tatsächlich vorkommende Zeichenfolgen werden mit semantischem
  `<mark>` ausgezeichnet.
- Die Markierung verwendet eine gelbe, kontrastreiche Darstellung und darf
  die Lesbarkeit oder Tastaturbedienung nicht beeinträchtigen.
- Groß-/Kleinschreibung beeinflusst die Erkennung nicht.
- Der eingegebene Begriff wird nicht künstlich in einem verwandten Treffer
  markiert, wenn er dort nicht vorkommt.
- Bei verwandten Treffern erscheint zusätzlich die Kennzeichnung
  `Thematisch verwandt`.

## Questions Rail Behavior

- Aktive Suche: Titel `Passende Fragen`, gefilterte Fragen, gleiche
  Hervorhebungsregeln wie in den übrigen Ergebnissen.
- Keine passende Frage: Text `Keine passenden Fragen gefunden.`
- Keine aktive Suche: bisherige allgemeine Fragenliste und bisheriger Titel.
- Das Fragenmodal verwendet bei aktiver Suche dieselbe gefilterte Datenbasis
  wie die rechte Leiste.

## Files Likely Affected

- `Webseite - Codex/app/support/search-discovery.php`
- `Webseite - Codex/app/support/platform-page.php`
- `Webseite - Codex/platform.php`
- `Webseite - Codex/search.php`

## Out of Scope

- Externe Suchmaschine oder Vektordatenbank.
- Vollständige semantische oder medizinische Ontologie.
- Automatische Synonympflege.
- Änderung der allgemeinen Feed-Personalisierung.
