# Humplore Teststrategie

Stand: 12. August 2026

## Aktueller Zustand

- Es gibt keine dokumentierte automatisierte Gesamttestsuite.
- Featureprüfungen bestehen derzeit aus PHP-Syntaxchecks, gezielten
  Datenbank-/HTTP-Smokes und manuellen Browserprüfungen.
- Historische Artefakte belegen erfolgreiche Einzeltests, aber kein vollständig
  grünes Release-Protokoll.

## Verbindliche Prüfung pro Änderung

Die konkrete Checkliste steht ausschließlich in
`openspec/changes/<change>/tasks.md`. Mindestens erforderlich sind:

1. `php -l` für jede geänderte PHP-Datei.
2. Erfolgs-, Fehler-, Login- und CSRF-Pfade für zustandsändernde Handler.
3. Prüfung vorhandener Kernabläufe auf Regression.
4. Desktop- und Mobile-Prüfung betroffener Oberflächen.
5. Aktualisierung des OpenSpec-Taskstatus erst nach tatsächlicher Prüfung.

## MVP-Abnahmematrix

Vor dem Launch müssen mindestens folgende Abläufe Ende-zu-Ende geprüft werden:

| Bereich | Kernabläufe |
|---|---|
| Einstieg | Landingpage, Registrierung, Login, Rechtstexte und Kontakt ohne Anmeldung; alle eigentlichen Produktinhalte und -funktionen nur nach Anmeldung |
| Account | Registrierung, Login, Logout, Profiländerung, Account-Löschung |
| Discovery | exakte Suche, Tippfehler, Vorschläge, kombinierte Filter, Leerzustände |
| Creator | Profil, Verifizierung, Folgen, Teilen, Direktnachricht |
| Beiträge | Text, Bild, Video, eindeutige Zuordnung zum pseudonymen Creator-Profil, Kommentar an/aus |
| Fragen | normal, anonym, direkte Antwort, Antwort als Beitrag |
| Engagement | `Neues gelernt!`, Kommentieren, Merken, Teilen |
| Moderation | Meldung je Inhaltstyp, Queue, Entscheidung, Sichtbarkeit, Sperre |
| Datenschutz | Anonymität von Fragen, interne Zuordnung, pseudonyme Creator-Namen, Löschablauf |

## Nicht funktionale Abnahme

- Mobile Breakpoints und Touch-Ziele
- Tastaturbedienung, Fokusführung und Screenreader-Beschriftungen
- WCAG-AA-Kontraste
- Zeichenkodierung und deutsche Sonderzeichen
- Seitenladezeit für Explore, Suche und Profile
- Lastverhalten mit realistischen Datenmengen
- Backup und Restore

## Bekannter Prüfbedarf

Der historische Regressionstest vom 14. Juni 2026 enthält zwei
30-Sekunden-Timeouts bei Plattformaufrufen. Dieser Ablauf muss mit der aktiven
Arbeitskopie reproduziert oder als behoben dokumentiert werden.
