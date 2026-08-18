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

## Aufgabenbezogene Pflichtpruefung

Die Pruefung muss proportional zur konkreten Aufgabe erfolgen:

- Bei reinen Text-, Konzept- oder Analyseaufgaben ohne Abhaengigkeit vom
  aktuellen Implementierungsstand nur die vom Nutzer genannten Unterlagen und
  den unmittelbar benoetigten Kontext lesen.
- Bei Aenderungen an einzelnen Dateien den Git-Arbeitsstand und nur die
  betroffenen Dateien sowie ihre direkten Abhaengigkeiten pruefen.
- Bei Implementierungen den Git-Arbeitsstand, den betroffenen Funktionsbereich
  und die dazugehoerige Dokumentation pruefen.
- Eine vollstaendige Projektpruefung nur bei Fragen zum Gesamtstatus, zur
  Roadmap, zu Prioritaeten oder fehlenden Funktionen sowie vor groesseren
  projektweiten Aenderungen und Veroeffentlichungen durchfuehren.
- Keine umfassende Projektanalyse starten, wenn sie fuer die konkrete Aufgabe
  keinen erkennbaren Mehrwert bietet.

Wenn eine vollstaendige Projektpruefung erforderlich ist:

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

## Produktentscheidungen und Grill-Me-Verfahren

Wenn eine Funktion, ein Konzept, eine Spezifikation, ein Prozess oder ein
groesserer Arbeitsbereich ausgearbeitet werden soll, darf Codex offene
fachliche oder nutzersichtbare Entscheidungen nicht stellvertretend treffen.
Diese Entscheidungen trifft der Nutzer im strukturierten Grill-Me-Verfahren.

1. Zuerst vorhandene verbindliche Entscheidungen aus aktueller Dokumentation
   und Code ermitteln. Bereits entschiedene Punkte nicht erneut oeffnen, ausser
   es besteht ein konkreter Widerspruch.
2. Danach alle fuer die Ausarbeitung wesentlichen offenen Produktfragen
   sammeln und dem Nutzer schrittweise in verstaendlicher Produktsprache
   stellen.
3. Zu jeder Frage Auswirkungen, Risiken und sinnvolle Alternativen erklaeren.
   Codex darf eine begruendete Empfehlung nennen, aber keine Option ohne
   ausdrueckliche Nutzerentscheidung als beschlossen behandeln.
4. Die Antworten des Nutzers zusammenfassen und vor der endgueltigen
   Ausarbeitung zur Bestaetigung vorlegen.
5. Erst nach der Bestaetigung verbindliche Produktdokumentation,
   OpenSpec-Proposal, Design und Tasks erstellen oder entsprechend
   finalisieren.
6. Wenn waehrend der Ausarbeitung eine weitere wesentliche Produktentscheidung
   sichtbar wird, die Arbeit an diesem Punkt pausieren und die Entscheidung
   erneut ueber Grill Me beim Nutzer einholen.

Technische Detailentscheidungen ohne relevante Auswirkung auf Produktumfang,
Nutzerverhalten, Datenschutz, Sicherheit, Moderation, Rollen oder Betrieb darf
Codex selbst treffen. Solche Entscheidungen sind nachvollziehbar zu begruenden
und in der passenden technischen Dokumentation festzuhalten.

Wenn `Grill Me` in der aktuellen Umgebung als Skill oder Workflow verfuegbar
ist, muss dieser verwendet werden. Andernfalls fuehrt Codex denselben Ablauf
als strukturierten Dialog durch.

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

## Dokumentation und Abschluss

Relevante Ergebnisse sollen in den meisten Aufgaben dauerhaft festgehalten
werden. Dazu gehoeren insbesondere getroffene Produktentscheidungen,
verbindliche Konzepte, geaendertes Verhalten, neue Erkenntnisse zum
Projektstand, Testergebnisse und bewusst offen gelassene Punkte.

- Zuerst die passende bestehende Dokumentation aktualisieren und keine zweite
  Datei mit demselben Zweck anlegen.
- `docs/projektstatus.md` aktualisieren, wenn sich Implementierungsstand,
  Teststatus, bekannte Fehler, Risiken oder naechste Schritte veraendern.
- Produktentscheidungen in der passenden Produktdokumentation festhalten;
  technische Entscheidungen in Architektur- oder Entwicklungsdokumentation.
- Bei einer Konzeptauswertung die beschlossenen Ergebnisse und offenen Fragen
  dokumentieren, sofern sie fuer Humplore weiterverwendet werden sollen.
- Rein unverbindliche Erklaerungen, kurze Rueckfragen und verworfene Ideen
  muessen nicht dauerhaft dokumentiert werden.
- Besteht keine passende Zieldatei, zuerst eine sinnvolle Einordnung nennen
  und nur bei Bedarf eine neue Dokumentationsdatei anlegen.
- Dokumentation und Code muessen sich nicht widersprechen. Veraltete Aussagen
  im direkt betroffenen Dokument sind bei der Aktualisierung zu entfernen.

Nach einer abgeschlossenen Implementierung:

1. Relevante Syntax-, Funktions- und Regressionstests ausfuehren.
2. Betroffene Dokumentation und gegebenenfalls `docs/projektstatus.md`
   widerspruchsfrei aktualisieren.
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
