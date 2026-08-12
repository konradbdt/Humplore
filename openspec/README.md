# Humplore OpenSpec

OpenSpec ist die einzige aktive Quelle für detaillierte Anforderungen,
technische Entscheidungen und Aufgaben einzelner Änderungen.

## Struktur

- `specs/`: aktuell gültiges Verhalten
- `changes/<name>/`: aktive, noch nicht vollständig abgenommene Änderung
- `changes/archive/<datum>-<name>/`: abgeschlossene Änderungshistorie
- `config.yaml`: projektweite OpenSpec-Regeln

## Aktive Änderungen

- `profile-discovery-filters`
- `topic-category-overview`

Der aktuelle Fortschritt steht ausschließlich in der jeweiligen `tasks.md`.
Statuslisten außerhalb von OpenSpec verlinken darauf und kopieren keine
Einzelaufgaben.

## Arbeitsablauf

1. Änderung unter `changes/<name>/` mit Proposal, Delta-Spec, Design und Tasks
   anlegen.
2. Tasks implementieren und erst nach tatsächlicher Prüfung abhaken.
3. Delta-Spec und Aufgaben validieren.
4. Abgeschlossene Änderung in `changes/archive/` verschieben und die Delta-Spec
   in `specs/` überführen.

Die frühere parallele SpecKit-Dokumentation ist nur noch unter
`docs/archive/spec-kit/` als Historie vorhanden.
