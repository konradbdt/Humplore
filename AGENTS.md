# Arbeitsregeln fuer Humplore

Diese Regeln gelten fuer alle Arbeiten in diesem Repository.

## Verbindliche Projektgrundlage

- Der aktive Codebaum ist `Webseite - Codex/`.
- `docs/archive/` enthaelt historische Anforderungen und fruehere Analysen,
  aber keinen verlaesslichen aktuellen Umsetzungsstand.
- Massgebliche Statusdatei ist `docs/projektstatus.md`. Ihre Aussagen muessen
  trotzdem mit dem aktuellen Code und Git-Stand abgeglichen werden.
- Nicht vorhandene oder verworfene Projektordner duerfen nicht aus alten
  Chatverlaeufen oder historischen Dokumenten als weiterhin vorhanden
  angenommen werden.

## Pflichtpruefung vor Status, Planung und Implementierung

Vor jeder Aussage zum aktuellen Projektstand und vor jeder neuen Aufgabe:

1. Aktuellen Branch mit `git branch --show-current` pruefen.
2. Arbeitsstand mit `git status --short` pruefen.
3. Die letzten Commits mit `git log -10 --oneline` pruefen.
4. `docs/projektstatus.md` vollstaendig lesen.
5. Betroffene Aussagen mit dem aktuellen Code in `Webseite - Codex/`
   abgleichen.
6. Relevante Ordner und Dateien mit einer Dateisystempruefung bestaetigen,
   bevor ihre Existenz oder Loeschung behauptet wird.
7. Nicht commitete Aenderungen ausdruecklich vom letzten commiteten Stand
   unterscheiden.

Fruehere Chats sind nur Kontext. Sie sind keine verlaessliche Quelle fuer den
aktuellen Stand, wenn Git, Dateisystem, Dokumentation oder Code inzwischen
geaendert wurden.

## Statusangaben und Nachweise

- `implementiert` bedeutet: Der Code ist im aktiven Codebaum vorhanden.
- `geprueft` bedeutet: Ein konkreter Test wurde erfolgreich ausgefuehrt und
  das Ergebnis ist nachvollziehbar dokumentiert.
- Historische Checklisten oder Smoke-Tests duerfen nicht als aktuelle
  Browser-Abnahme ausgegeben werden.
- Bei widerspruechlichen Angaben gilt der aktuell gepruefte Code- und
  Teststand. Der Widerspruch in der Dokumentation ist anschliessend zu
  bereinigen.
- Eine Aenderung ist erst auf GitHub, wenn der betreffende Commit erfolgreich
  auf den genannten Remote-Branch gepusht wurde. Branch und Commit sind dabei
  anzugeben.
- Eine Aenderung auf einem Arbeitsbranch darf nicht als in `main` vorhanden
  bezeichnet werden, solange sie nicht nach `main` gemergt wurde.

## Abschluss jeder Aufgabe

Nach einer abgeschlossenen Implementierung:

1. Relevante Syntax-, Funktions- und Regressionstests ausfuehren.
2. `docs/projektstatus.md` vollstaendig und widerspruchsfrei aktualisieren.
3. Geaenderte Dateien und Testergebnisse nennen.
4. Offene Einschraenkungen klar dokumentieren.
5. Vor dem Staging pruefen, dass keine fremden oder lokalen Artefakte in den
   Commit gelangen.

## Git- und GitHub-Arbeitsweise

- Codex bearbeitet und prueft die Dateien, fuehrt in dieser Umgebung aber
  keine Git-Schreiboperationen aus, wenn `.git` nur lesbar ist.
- Codex darf nicht behaupten, Aenderungen gestaged, committed, gepusht oder
  nach `main` gemergt zu haben, solange der jeweilige Befehl nicht nachweislich
  erfolgreich war.
- Nach jeder abgeschlossenen Aufgabe gibt Codex stattdessen kopierbare,
  projektspezifische PowerShell-Befehle aus fuer:
  1. Kontrolle der betroffenen Dateien,
  2. gezieltes Staging ausschliesslich dieser Dateien,
  3. Kontrolle des Staging-Bereichs,
  4. Commit mit passender Nachricht,
  5. Abruf und Rebase des aktuellen Remote-Branches,
  6. Push des aktuellen Branches.
- Keine pauschalen Befehle wie `git add .` oder `git add -A` empfehlen, wenn
  fremde, lokale oder ungetrackte Dateien vorhanden sind.
- Vor dem Commit soll `git diff --cached --name-only` kontrolliert werden.
- Bei Rebase-Konflikten, abgelehntem Push oder unerwarteten Dateien soll der
  Nutzer stoppen und die Terminalausgabe zur Pruefung senden.
- Das Zusammenfuehren nach `main` wird als eigener, ausdruecklicher Schritt
  behandelt und nicht mit dem Push eines Arbeitsbranches gleichgesetzt.

## Schutz lokaler und fremder Aenderungen

- `Webseite - Codex/data/database.db` ist lokale Testdatenbank und darf nicht
  unbeabsichtigt gestaged oder committed werden.
- Ungetrackte PDF-, Build-, Extraktions- und temporaere Dateien duerfen nicht
  ohne ausdruecklichen Auftrag hinzugefuegt werden.
- Vorhandene Aenderungen des Nutzers sind zu erhalten.
- Keine Dateien oder Ordner ausserhalb des ausdruecklich beauftragten Umfangs
  loeschen, verschieben oder committen.
