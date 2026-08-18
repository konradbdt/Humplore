# Humplore Produktumfang

Stand: 12. August 2026

Dieses Dokument enthält ausschließlich verbindliche Produktentscheidungen. Der
Umsetzungsstand steht in [`projektstatus.md`](projektstatus.md), die Reihenfolge
der nächsten Arbeiten in [`../funktions-roadmap.md`](../funktions-roadmap.md).

## Launch-Kriterien

- Das Admin-Moderationsdashboard ist einsatzbereit.
- Datenschutz, AGB, Impressum und ein Kontaktweg für Datenlöschung sind
  vollständig vorhanden.
- Alle MVP-Funktionen funktionieren auf Mobilgeräten.
- Eine vollständige funktionale, sicherheitsbezogene und mobile Testphase ist
  dokumentiert und abgeschlossen.

## Bestandteil des MVP

- Text-, Bild- und Videobeiträge.
- Creator dürfen unabhängig von ihrer Verifizierung veröffentlichen.
- Account-Löschung.
- Anonyme Fragen an Creator.
- Meldungen für Beiträge, Fragen und Kommentare.
- Post-Aktionen: `Neues gelernt!`, `Kommentieren`, `Merken` und `Teilen`.
- Eine eigene Ansicht für gemerkte Beiträge.
- Creator-Profile folgen und teilen.
- Direktnachrichten vom Creator-Profil aus starten.
- Creator können Kommentare für eigene Beiträge deaktivieren.
- Fragen sind über Suche, Filter oder eine eigene Struktur auffindbar.

## Nicht Bestandteil des MVP

- Anonyme Creator-Beiträge oder eine beitragsbezogene
  Anonymitätseinstellung; beides ist auch außerhalb des MVP nicht vorgesehen.
- Datenexport.
- Monetarisierung, Werbung, Donation und Creator-Auszahlungen.
- Profilmeldungen.
- Sammlungen oder Ordner für gemerkte Beiträge.
- Individuelle Sichtbarkeitsschalter für Ort, Sprache oder Profilbild.
- Vollständige Feed-Personalisierung auf Basis von Interessen.

## Creator- und Profilregeln

- Creator dürfen einen frei gewählten, auch fiktiven Nutzernamen verwenden.
  Beiträge bleiben diesem öffentlichen Creator-Profil zugeordnet; eine
  zusätzliche Anonymisierung einzelner Beiträge ist nicht vorgesehen.
- Die Verifizierung ist keine Voraussetzung für das Veröffentlichen.
- Eine Verifizierung bestätigt einen Creator für ein bestimmtes Thema und
  wird als Vertrauenssignal sichtbar gemacht.
- Beiträge verifizierter Creator werden in Suche und Discovery bevorzugt
  ausgespielt. Kriterien und Stärke dieses Ranking-Vorteils müssen vor der
  Implementierung festgelegt und nachvollziehbar geprüft werden.
- Pflichtangaben sind Username, Kategorie oder Thema und Bio.
- Beruf ist kein separates Pflichtfeld.
- Erweiterte Profilfilter wie Altersgruppe, Herkunftsland und
  Geschlecht/Identität sind optional und müssen sensible Daten sowie eine
  ausdrückliche Filtereinwilligung berücksichtigen.

## Anonymitätsregeln

- Anonymität als Inhaltsfunktion gibt es ausschließlich für Fragen.
- Bei anonymen Fragen sehen Creator und andere Nutzer keine Identität des
  Fragenden.
- Die Betreiber behalten aus Sicherheits- und Moderationsgründen die interne
  Account-Zuordnung anonymer Fragen.
- Anonyme Fragen erhalten keine stabilen öffentlichen Ersatzkennungen, über
  die mehrere Fragen derselben Person gruppiert werden könnten.
- Creator-Beiträge sind nie anonym, können aber unter einem frei gewählten,
  fiktiven Nutzernamen veröffentlicht werden.

## Zugangsregeln

- Die eigentlichen Humplore-Funktionen und -Inhalte sind nur nach Anmeldung
  nutzbar. Dazu gehören insbesondere Explore, Suche, Profile, Beiträge,
  Fragen, Kommentare, Folgen, Merken und Melden.
- Ohne Anmeldung sind nur die für den Zugang und den rechtlichen Betrieb
  erforderlichen Seiten erreichbar: Einstieg, Registrierung, Login,
  Rechtstexte und Kontakt.
- Direkte Aufrufe geschützter Seiten und zustandsändernder Endpunkte müssen
  serverseitig abgewiesen oder zum Login geführt werden.

## Moderationsregeln

- Meldbare Objekte im MVP sind Beiträge, Fragen und Kommentare.
- Zulässige Gründe enthalten auch Falschinformationen sowie falsche oder
  problematische Empfehlungen.
- Gemeldete Inhalte bleiben sichtbar, bis ein Betreiber entscheidet.
- Betreiber können Inhalte nach Prüfung temporär ausblenden, dauerhaft
  ausblenden oder löschen.
- Je nach Schwere und Wiederholung können Ersteller verwarnt oder gesperrt
  werden.
- Zum Launch bearbeiten die beiden Betreiber die Moderationsqueue selbst.

## Noch zu entscheidende Produktfragen

1. Welche Nachweise müssen Creator einreichen?
2. Wer prüft Creator und welche Statusübergänge gibt es?
3. Nach welchen Kriterien und mit welcher Gewichtung werden Beiträge
   verifizierter Creator in Suche und Discovery bevorzugt?
4. Beginnt eine Direktnachricht nur einen Thread oder umfasst das MVP bereits
   Inbox, Benachrichtigungen, Blockieren und Melden?
5. Welche Videoformate, Dateigrößen und Laufzeiten sind zulässig?
6. Bleibt SQLite bis zum Launch oder wird verbindlich auf MySQL migriert?
7. Welche messbaren Abnahmekriterien gelten für Last, Ladezeit und mobile QA?
8. Die frühere Aussage zur Auslagerung von Bildern oder Medien vor dem MVP ist
   unklar und muss fachlich bestätigt werden.
