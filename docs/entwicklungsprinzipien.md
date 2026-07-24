# Humplore Entwicklungsprinzipien

Stand: 24. Juli 2026

## Produktprinzipien

1. **Erfahrungen auffindbar machen:** Suche, Kategorien und Profile helfen
   Nutzern auch ohne exakte Begriffe, passende Erfahrungen zu finden.
2. **Vertrauen und Privatsphäre:** Vertrauenssignale und
   Privatsphäreentscheidungen sind sichtbar, ohne Klarnamen zu erzwingen.
3. **Kompatible Schritte:** Öffentliche Routen und vorhandene Daten bleiben bei
   inkrementellen Änderungen kompatibel.
4. **MVP vor Erweiterung:** Launch-Blocker haben Vorrang vor Monetarisierung,
   Personalisierung und Komfortfunktionen.

## Technische Prinzipien

- Routen bleiben möglichst dünn; wiederverwendbare Logik gehört nach
  `app/support/`.
- Schemaänderungen sind additiv und rückwärtskompatibel.
- Neue Abhängigkeiten werden nur eingeführt, wenn vorhandene PHP-Muster nicht
  ausreichen.
- Datenbankzugriffe verwenden vorbereitete Statements.
- Ausgaben werden kontextgerecht escaped.
- Änderungen gelten erst als abgeschlossen, wenn die OpenSpec-Aufgaben und
  zugehörigen Prüfungen abgeschlossen sind.

## Entscheidungsreihenfolge

1. Verbessert die Änderung Discovery, Vertrauen, Sicherheit oder einen
   bestätigten MVP-Workflow?
2. Erhält sie bestehende URLs und Daten?
3. Ist das Verhalten lokal und reproduzierbar prüfbar?
4. Ist klar, welche nachfolgende MVP-Funktion darauf aufbaut?

