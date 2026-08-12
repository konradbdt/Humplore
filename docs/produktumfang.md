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
- Anonyme Creator-Beiträge ohne öffentliche Creator-Zuordnung.
- Meldungen für Beiträge, Fragen und Kommentare.
- Post-Aktionen: `Neues gelernt!`, `Kommentieren`, `Merken` und `Teilen`.
- Eine eigene Ansicht für gemerkte Beiträge.
- Creator-Profile folgen und teilen.
- Direktnachrichten vom Creator-Profil aus starten.
- Creator können Kommentare für eigene Beiträge deaktivieren.
- Fragen sind über Suche, Filter oder eine eigene Struktur auffindbar.

## Nicht Bestandteil des MVP

- Datenexport.
- Monetarisierung, Werbung, Donation und Creator-Auszahlungen.
- Profilmeldungen.
- Sammlungen oder Ordner für gemerkte Beiträge.
- Individuelle Sichtbarkeitsschalter für Ort, Sprache oder Profilbild.
- Vollständige Feed-Personalisierung auf Basis von Interessen.

## Creator- und Profilregeln

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

- Bei anonymen Fragen sehen Creator und Besucher keine Identität des Fragenden.
- Bei anonymen Creator-Beiträgen sind im Explore-Feed weder Creator-Zuordnung
  noch öffentlicher Profil-Link sichtbar.
- Die Betreiber behalten aus Sicherheits- und Moderationsgründen die interne
  Zuordnung zum Account.
- Anonyme Inhalte erhalten keine stabilen öffentlichen Ersatzkennungen, über
  die Beiträge derselben Person gruppiert werden könnten.

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
