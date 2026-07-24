# Humplore

Stand: 24. Juli 2026

Dieses Dokument ist der Einstiegspunkt in das Projekt. Produktentscheidungen,
Status, Prioritäten und technische Details werden bewusst getrennt gepflegt.

## Verbindliche Dokumente

| Frage | Verbindliche Quelle |
|---|---|
| Was gehört zum MVP und welche Regeln gelten? | [`docs/produktumfang.md`](docs/produktumfang.md) |
| Welche Vision und Zielgruppen leiten das Produkt? | [`docs/vision-und-zielgruppen.md`](docs/vision-und-zielgruppen.md) |
| Was ist aktuell umgesetzt, teilweise umgesetzt oder offen? | [`docs/projektstatus.md`](docs/projektstatus.md) |
| Was wird als Nächstes bearbeitet? | [`funktions-roadmap.md`](funktions-roadmap.md) |
| Wie ist die Anwendung technisch aufgebaut? | [`docs/architektur.md`](docs/architektur.md) |
| Welche Fachbegriffe gelten? | [`docs/glossar.md`](docs/glossar.md) |
| Wie wird geprüft und abgenommen? | [`docs/teststrategie.md`](docs/teststrategie.md) |
| Welche Entwicklungsprinzipien gelten? | [`docs/entwicklungsprinzipien.md`](docs/entwicklungsprinzipien.md) |
| Welche Anforderungen und Tasks gelten für eine konkrete Änderung? | [`openspec/README.md`](openspec/README.md) und der jeweilige OpenSpec-Change |

Status und Aufgaben werden nicht zusätzlich in Feature-Specs, Prompts oder
Verbesserungslisten gespiegelt. Die Checkboxen im jeweiligen
`openspec/changes/<change>/tasks.md` sind die einzige detaillierte Aufgabenliste
für aktive Änderungen.

## Codebereiche

- `Webseite - Redesign/` ist die am 24. Juli 2026 zuletzt bearbeitete
  Arbeitskopie. Sie enthält das neue Landingpage-Redesign.
- `Webseite - Codex/` ist der vorherige, fast identische Stand. Bis die
  Arbeitskopie ausdrücklich bestätigt und als konsolidierter Stand versioniert
  ist, dient er nur als Vergleichsbasis.

Die beiden Codebäume dürfen nicht parallel weiterentwickelt werden. Neue
Dokumentation und OpenSpec-Artefakte verweisen auf `Webseite - Redesign/`.

## Dokumentationsregeln

1. Produktentscheidungen nur in `docs/produktumfang.md` ändern.
2. Nach Implementierung oder Abnahme `docs/projektstatus.md` aktualisieren.
3. Prioritäten nur in `funktions-roadmap.md` ordnen.
4. Detailanforderungen und Tasks ausschließlich in OpenSpec pflegen.
5. Abgeschlossene OpenSpec-Changes nach erfolgreicher Validierung archivieren.
6. Historische SpecKit-Dokumente nicht mehr als aktuellen Status verwenden.
7. Historische AsciiDoc-Vorlagen nicht als Projektanforderungen verwenden.
