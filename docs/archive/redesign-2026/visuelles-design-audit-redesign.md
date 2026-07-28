# Visueller Design-Audit der Humplore-Plattform

> Archiviert am 28. Juli 2026: Dieser Audit bezieht sich ausschließlich auf
> den verworfenen Baum `Webseite - Redesign/` und ist keine aktuelle
> Qualitätsbewertung von Humplore.

Stand: 28. Juli 2026

## Auftrag

Dieser Audit untersucht ausschließlich das visuelle Design der bestehenden
Arbeitskopie `Webseite - Redesign/`.

Es wurden keine Änderungen am Design oder am Programmcode vorgenommen.

## Geprüfte Ansichten

Untersuchte Seiten:

- Startseite
- Login
- Registrierung
- Explore
- Suche
- Profil
- Beitragserstellung
- News

Geprüfte Darstellungsgrößen:

- Desktop: 1280 × 720 Pixel
- Mobile: 390 × 844 Pixel

## Gesamturteil

Die visuelle Grundidee von Humplore ist erkennbar, aber die Plattform besitzt
momentan kein geschlossenes Designsystem. Desktop ist teilweise nutzbar, wirkt
jedoch unruhig, dicht und stellenweise instabil. Mobile enthält mehrere echte
Layout- und Navigationsfehler und ist in diesem Zustand nicht abnahmefähig.

Die größten Ursachen sind:

- widersprüchliche CSS-Regeln,
- zu viele Sonderlösungen für einzelne Seiten,
- unkontrollierte Sticky- und Fixed-Elemente,
- fehlende gemeinsame Komponenten,
- inkonsistente Breakpoints,
- mehrere parallele Typografie- und Farbsysteme.

## Größte visuelle Probleme

### 1. Schwere Überlappungen auf der mobilen Startseite

- Erfahrungsbeispiel, Mission und nachfolgender Funktionsbereich liegen
  teilweise übereinander.
- Überschrift und Funktionskarten werden in zwei extrem schmale Spalten von
  ungefähr 168 und 181 Pixeln gepresst.
- Die Funktionskarten beginnen, bevor der vorherige Bereich visuell beendet
  ist.
- Inhalte erscheinen dadurch abgeschnitten, gequetscht oder falsch
  übereinandergeschoben.

### 2. Sticky-Elemente verdecken Inhalte

- In der mobilen Suche bleibt eine ungefähr 282 Pixel hohe Suchkarte beim
  Scrollen stehen.
- Diese Suchkarte überlagert sowohl den Header als auch Beitragskarten.
- Auf Desktop verdeckt die große Sticky-Suche ebenfalls Teile der Ergebnisse.
- Der Profilkopf bleibt auf Desktop zu groß im sichtbaren Bereich und schiebt
  sich über Navigation, Fragen und Inhaltsbereiche.
- Mehrere Seiten kombinieren Sticky-Header, Sticky-Suche und fixierte
  Navigationen ohne ein gemeinsames Höhen- und Ebenenkonzept.

### 3. Mobile Navigation fehlt auf wichtigen Seiten

- Explore, Profil und News blenden die seitlichen Navigationen mobil aus.
- Ein konsistenter mobiler Ersatz fehlt auf diesen Seiten.
- Auf dem mobilen Profil ist im sichtbaren Bereich praktisch nur das Logo als
  Navigationsmöglichkeit vorhanden.
- Suche und Beitragserstellung besitzen eine Bottom-Navigation, andere
  Kernseiten nicht.
- Die Bottom-Navigation erscheint teilweise auch auf Desktop und verdeckt dort
  Inhalte.

### 4. Zu viele widersprüchliche Layoutregeln

Bei der Analyse wurden folgende Größenordnungen festgestellt:

| Datei | Media Queries | `!important`-Regeln | Sticky | Fixed |
|---|---:|---:|---:|---:|
| `platform.php` | 25 | 95 | 7 | 4 |
| `profile.php` | 35 | 553 | 10 | 6 |
| `search.php` | 5 | 1 | 3 | 1 |
| `posten.php` | 6 | 1 | 2 | 0 |
| `news.php` | 4 | 0 | 2 | 0 |

Besonders im Profil überschreiben sich Desktop-, Tablet- und Mobile-Regeln
mehrfach gegenseitig. Die sichtbaren Fehler sind deshalb nicht isoliert,
sondern strukturell bedingt.

### 5. Inkonsistente Typografie

- Poppins, DM Serif Display, Lora, Georgia und teilweise Arial werden gemischt.
- Profil- und Beitragskarten wechseln innerhalb derselben Ansicht zwischen
  Sans-Serif und Serifenschrift.
- Die Schriftmischung erzeugt keine klare redaktionelle Hierarchie, sondern
  wirkt zufällig.
- Metatexte sind teilweise sehr klein oder kontrastarm.
- Kartenüberschriften, Seitentitel und Inhaltsüberschriften folgen keinem
  gemeinsamen Größensystem.

### 6. Sichtbare Zeichenkodierungsfehler

Mehrere Texte und Platzhalter erscheinen beschädigt, beispielsweise:

- `BeitrÃ¤ge`
- `WÃ¤hle`
- `WorÃ¼ber`
- weitere Umlaute und Sonderzeichen

Diese Fehler beeinträchtigen die Lesbarkeit und lassen die Oberfläche sichtbar
unfertig wirken.

### 7. Explore ist deutlich zu dicht

- Navigation, Kategorien, Themen, Creator, Vorschauen, Fragen und Feed
  konkurrieren gleichzeitig um Aufmerksamkeit.
- Der zentrale Desktopbereich ist für zweispaltige Beitragskarten zu schmal.
- Aktionsleisten werden gequetscht oder seitlich abgeschnitten.
- Karten enthalten weitere verschachtelte Karten, Badges und Inhaltsblöcke.
- Mobil ist allein die Themenübersicht rund 3.900 Pixel hoch.
- Die mobile Explore-Seite erreicht insgesamt mehr als 10.000 Pixel Höhe.
- Der eigentliche Feed beginnt dadurch sehr spät.

### 8. Unausgewogene Größenverhältnisse und Weißraum

- Auf dem Profil entstehen sehr große leere Flächen, wenn keine Beiträge
  vorhanden sind.
- Die mobile Profilvorstellung beansprucht ungefähr 714 Pixel, bevor der
  eigentliche Inhalt beginnt.
- Die Beitragserstellung nutzt auf Desktop nur einen Teil der großen Karte,
  während rechts viel ungenutzter Raum verbleibt.
- News besteht größtenteils aus leerer Fläche und einem dauerhaft sichtbaren
  Ladeindikator.
- Andere Bereiche sind im direkten Gegensatz dazu stark überfüllt.

### 9. Kontrastprobleme

Gemessene Kontrastverhältnisse:

| Kombination | Kontrast | Bewertung |
|---|---:|---|
| Weiß auf Primärgrün `#526238` | 6,64:1 | gut |
| Weiß auf Dunkelgrün `#354326` | 10,59:1 | sehr gut |
| Haupttext auf Papierhintergrund | 12,71:1 | sehr gut |
| Gedämpfter Text auf Papierhintergrund | 4,34:1 | unter WCAG AA für normalen Text |
| Weißer Auth-Link auf weißer Karte | 1:1 | nicht lesbar |

Der Link „Neu hier? Jetzt registrieren“ ist auf der Login-Karte weiß auf weiß
und damit visuell unsichtbar.

Zusätzlich passen dunkelblaue Buttons in Meldeformularen und violette
Fokusfarben nicht zur übrigen Farbpalette.

### 10. Inkonsistente Komponenten

- Karten verwenden unterschiedliche Radien, Schatten, Rahmen und
  Innenabstände.
- Buttons wechseln zwischen Pillen, rechteckigen Schaltflächen, kleinen
  Textbuttons und dunklen Sondervarianten.
- Meldeformulare wirken wie Bestandteile eines anderen Produkts.
- Die Formulare nehmen besonders im Fragenbereich sehr viel Raum ein.
- Icons bestehen aus Punkten, Buchstaben, `#`, `+`, Pfeilen und verschiedenen
  Symbolstilen.
- Es existiert kein erkennbares gemeinsames Iconset.

## Bewertung nach Bereichen

| Bereich | Befund |
|---|---|
| Visuelle Hierarchie | Auf der Landingpage teilweise stark, innerhalb der Plattform unklar |
| Seitenaufbau | Zu viele Sonderlayouts und negative Überlagerungen |
| Abstände | Uneinheitlich, besonders zwischen Karten, Headern und Sidebars |
| Größenverhältnisse | Zu große Sticky-Bereiche, zu schmale Karten und überdimensionierte Leerflächen |
| Typografie | Deutlich inkonsistent |
| Farben | Grundpalette passend, Anwendung aber nicht konsequent |
| Kontraste | Primärbuttons gut, mehrere Neben- und Auth-Texte problematisch |
| Karten | Inhaltlich sinnvoll, visuell zu viele Varianten und Verschachtelungen |
| Buttons | Funktional erkennbar, aber ohne einheitliche Hierarchie |
| Formulare | Beitragserstellung grundsätzlich gut gruppiert; Melde- und Auth-Formulare problematisch |
| Tabellen | In den produktiven Seiten sind keine relevanten Tabellen vorhanden |
| Navigation | Desktop komplex, mobil teilweise vollständig fehlend |
| Icons | Kein einheitliches Iconset |
| Weißraum | Wechsel zwischen zu wenig und extrem viel |
| Informationsdichte | Explore deutlich überladen |
| Seitenkonsistenz | Niedrig |
| Responsive Design | Mehrere Release-Blocker |

## Seitenspezifische Bewertung

### Startseite

#### Desktop

Stärken:

- Der obere Hero-Bereich besitzt eine klare Hauptaussage.
- Das konkrete Erfahrungsbeispiel vermittelt den Produktzweck.
- Die Markenfarbe und die großen Überschriften erzeugen Aufmerksamkeit.

Probleme:

- Der Hero ist sehr groß und schiebt weiterführende Inhalte weit nach unten.
- Nachfolgende Bereiche folgen nicht vollständig derselben gestalterischen
  Logik.
- Beim durchgehenden Rendern entstehen sichtbare Layoutinstabilitäten.

#### Mobile

- Mehrere Abschnitte überlappen sich geometrisch.
- Die Feature-Sektion bleibt fälschlicherweise zweigeteilt.
- Die Überschrift und die Karten werden extrem schmal.
- Der Hero ist mit ungefähr 844 Pixeln fast eine vollständige Bildschirmhöhe
  hoch.
- Die nachfolgende Erfahrungsdarstellung verlängert den Einstieg zusätzlich.

### Explore

#### Desktop

- Dreispaltige Struktur aus Navigation, Inhalt und Fragen ist grundsätzlich
  nachvollziehbar.
- Der Mittelbereich wird durch die beiden Seitenleisten jedoch zu schmal.
- Themenkarten, Unterkarten, Badges und Beitragsvorschauen erzeugen eine sehr
  hohe Informationsdichte.
- Beitragsaktionen werden in schmalen Karten teilweise abgeschnitten.

#### Mobile

- Seitenleisten werden sinnvollerweise ausgeblendet.
- Dadurch verschwindet jedoch gleichzeitig die Hauptnavigation.
- Die Themenübersicht wird extrem lang.
- Nutzer erreichen den Feed erst nach mehreren Bildschirmhöhen.
- Der mobile Header benötigt ungefähr 133 Pixel Höhe.

### Suche

#### Desktop

- Trefferzahlen und Trennung nach Profilen und Beiträgen schaffen Orientierung.
- Profilkarten sind zu breit und enthalten dadurch viel leere Fläche.
- Die fixierte Bottom-Navigation liegt über den Beiträgen.
- Große Medien dominieren einzelne Suchkarten unverhältnismäßig.
- Die Sticky-Suche verdeckt beim Scrollen den Inhalt.

#### Mobile

- Kartenbreiten und einspaltiger Feed funktionieren grundsätzlich.
- Die Sticky-Suchkarte ist mit ungefähr 282 Pixeln zu hoch.
- Sie überlagert beim Scrollen große Teile der Beitragskarten.
- Die Bottom-Navigation nimmt zusätzlich dauerhaft Platz über dem Inhalt ein.

### Profil

#### Desktop

- Profilbild, Thema, Bio und Statistiken sind sinnvoll gruppiert.
- Der Profilkopf besitzt eine brauchbare Informationsarchitektur.
- Der große Sticky-Profilkopf verdeckt beim Scrollen jedoch andere Inhalte.
- Fragen, Meldeformular und Navigation konkurrieren mit dem Profilinhalt.
- Bei fehlenden Beiträgen entsteht eine sehr große leere Fläche.

#### Mobile

- Profilinformationen werden einspaltig dargestellt.
- Der Profilkopf ist mit ungefähr 714 Pixeln zu lang.
- Seitenleiste und Fragenbereich werden vollständig ausgeblendet.
- Damit verschwindet eine zentrale Produktfunktion.
- Eine mobile Hauptnavigation fehlt.

### Beitragserstellung

#### Desktop

- Labels, Zeichenzähler, Kategorien und Uploadbereich sind grundsätzlich
  verständlich.
- Die linke Formularspalte ist klar gegliedert.
- Der verfügbare Raum wird schlecht genutzt.
- Unterhalb des Uploadbereichs bleibt eine große leere rechte Fläche.
- Die fixierte Navigation liegt über Formularbereichen.
- Der abschließende Button wirkt im Verhältnis zum Formular zu klein und
  uneindeutig platziert.

#### Mobile

- Das Formular wird korrekt einspaltig.
- Uploadbereich und Eingaben besitzen brauchbare Breiten.
- Der Header ist mit ungefähr 125 Pixeln recht hoch.
- Die Bottom-Navigation überlagert den sichtbaren unteren Bereich.
- Zeichenkodierungsfehler sind in Labels und Platzhaltern deutlich sichtbar.

### News

#### Desktop

- Die Seite besitzt eine klare, aber sehr leere Grundstruktur.
- Navigation und großer „Coming Soon“-Bereich wirken nicht miteinander
  verbunden.
- Der Ladeindikator vermittelt einen aktiven Ladevorgang, obwohl die Seite ein
  statischer Leerzustand ist.

#### Mobile

- Die seitliche Navigation wird ausgeblendet.
- Eine Bottom-Navigation wird nicht eingeblendet.
- Die Seite wird dadurch zu einer visuellen Sackgasse.
- Der Leerzustand erklärt keinen sinnvollen nächsten Schritt.

### Login und Registrierung

Stärken:

- Die Formulare sind einfach und leicht verständlich.
- Labels, Eingabefelder und Primärbutton sind klar zu erkennen.
- Die dunkelgrüne Fläche passt grundsätzlich zur Marke.

Probleme:

- Auth-Seiten verwenden eine reine Wortmarke, während die Plattform das
  eigentliche Logo nutzt.
- Die Karten sind besonders mobil unnötig schmal.
- Das mobile Loginformular ist nur ungefähr 247 Pixel breit.
- Der Sekundärlink ist weiß auf weiß und unsichtbar.
- Der Linktext liegt mit ungefähr 13,76 Pixeln unter der dokumentierten
  Mindestschriftgröße von 14 Pixeln.

## Gestalterische Stärken

- Die olivgrüne und warme helle Grundpalette passt zur Idee einer ruhigen,
  menschlichen Erfahrungsplattform.
- Weiß auf dem Primärgrün besitzt einen guten Kontrast.
- Der obere Desktopbereich der Landingpage vermittelt den USP klar.
- Das konkrete Erfahrungsbeispiel mit Marlene erklärt das Produkt besser als
  abstrakte Marketingelemente.
- Themen-, Kategorie- und Profilinformationen sind grundsätzlich sinnvoll
  strukturiert.
- Ergebniszahlen in der Suche schaffen Orientierung.
- Profilbild, Thema, Bio und Statistiken bilden eine brauchbare
  Informationsbasis.
- Die Beitragserstellung verwendet verständliche Feldgruppen, Labels,
  Zeichenzähler und eine erkennbare Uploadfläche.
- Einige mobile Aktionsbuttons besitzen bereits ausreichend große Touch-Ziele.
- Die Kartenstruktur ist grundsätzlich geeignet, unterschiedliche
  Erfahrungsinhalte voneinander zu trennen.

## Empfohlene neue Designrichtung

Humplore sollte wie eine **ruhige, vertrauenswürdige redaktionelle
Wissensplattform** wirken: menschlicher als eine Datenbank, aber strukturierter
und glaubwürdiger als klassisches Social Media.

### Typografie

- Eine gemeinsame Sans-Serif-Schrift für die gesamte Anwendung verwenden,
  beispielsweise Poppins oder Inter.
- Eine Serifenschrift höchstens für große Marketingüberschriften der
  Landingpage einsetzen.
- Innerhalb der Anwendung keine wechselnden Serifenschriften verwenden.
- Einheitliche Typografiestufen für Seitentitel, Abschnittstitel, Kartentitel,
  Fließtext und Metadaten definieren.
- Keine regulären Texte unter 14 Pixeln verwenden.

### Farben

- Olivgrün als primäre Markenfarbe beibehalten.
- Warmes Papierweiß als ruhigen Seitenhintergrund verwenden.
- Einen zurückhaltenden warmen Akzent, beispielsweise Terrakotta, definieren.
- Violette Fokusfarben und dunkelblaue Sonderbuttons entfernen.
- Alle Textfarben auf WCAG-AA-Kontrast prüfen.

### Abstände und Größen

- Ein einheitliches 8-Pixel-Abstandssystem verwenden.
- Keine negativen Abstände zwischen Banner und Inhalt einsetzen.
- Einheitliche Containerbreiten definieren:
  - Anwendung: ungefähr 1180 Pixel,
  - Lesebereich: ungefähr 680 Pixel,
  - Seitenleisten: ungefähr 280 bis 320 Pixel.
- Mobile Seiten konsequent als eine Hauptspalte gestalten.

### Karten

- Einheitlicher Radius zwischen 12 und 16 Pixeln.
- Feiner Rahmen anstelle starker Schatten.
- Ein gemeinsamer Innenabstand für alle Standardkarten.
- Keine unnötigen Karten innerhalb weiterer Karten.
- Beitragskarten auf Desktop höchstens zweispaltig und nur bei ausreichender
  Breite.
- Beitragskarten auf Mobile immer einspaltig.

### Navigation

- Einen gemeinsamen App-Header für alle eingeloggten Seiten entwickeln.
- Auf Mobile dieselbe Bottom-Navigation auf allen Kernseiten anzeigen.
- Seitliche Navigationen auf Mobile nicht ersatzlos ausblenden.
- Suche mobil kompakt oder aufklappbar gestalten.
- Große Sticky-Flächen vermeiden.

### Responsive Gestaltung

- Mobile zuerst gestalten und anschließend für größere Viewports erweitern.
- Unterhalb von ungefähr 900 Pixeln keine festen Seitenleisten verwenden.
- Inhalte aus Seitenleisten in Tabs, Filterflächen, Drawer oder aufklappbare
  Bereiche verschieben.
- Bei 390 Pixel Breite dürfen keine geteilten Inhaltsbereiche oder schmalen
  Parallelspalten verbleiben.
- Sticky- und Fixed-Elemente müssen eine gemeinsame Höhen- und
  Ebenenberechnung verwenden.

## Elemente, die beibehalten werden sollten

- Humplore-Logo
- olivgrüne Markenrichtung
- warmer heller Hintergrund
- Story- und Erfahrungsbeispiel als zentrales Gestaltungsmotiv
- Themen- und Kategorie-Pills
- Ergebniszahlen der Suche
- Profilbild, Thema, Bio und Profilstatistiken
- Discovery-Modell aus Themen, Kategorien, Creatorn und Beiträgen
- Beitragsaktionen wie Lernen, Kommentieren, Merken und Teilen
- Uploadfläche, Labels und Zeichenzähler der Beitragserstellung
- Grundidee klar voneinander getrennter Inhaltskarten

## Elemente, die neu gestaltet werden sollten

- gesamter App-Header
- Desktop- und Mobile-Navigation
- mobile Landingpage-Struktur
- Explore-Seitenaufbau
- Themenübersicht
- seitliche Fragenleiste
- Beitragskarten
- Medienverhältnisse
- Aktionsleisten
- Sticky-Verhalten der Suche
- Sticky-Verhalten des Profilkopfs
- Profilfragen und Meldeformulare
- Suchergebnisdarstellung
- Desktopaufteilung der Beitragserstellung
- News-Leerzustand
- Login- und Registrierungskarten
- Sekundärlinks auf Auth-Seiten
- vollständige Icon-Sprache
- Typografie-, Abstands-, Farb-, Button- und Kartenstandards
- sämtliche sichtbaren Zeichenkodierungsfehler

## Empfohlene Reihenfolge eines späteren Redesigns

1. Gemeinsame Design-Tokens festlegen.
2. Typografie, Farben, Abstände und Kontraste vereinheitlichen.
3. App-Header und Navigation für Desktop und Mobile entwickeln.
4. Mobile Überlappungen und verschwindende Navigationen beseitigen.
5. Explore-Seite und Beitragskarten neu strukturieren.
6. Suche und Sticky-Verhalten überarbeiten.
7. Profilkopf, Fragenbereich und Meldeformulare neu aufbauen.
8. Beitragserstellung räumlich neu ordnen.
9. Auth-Seiten und News-Leerzustand angleichen.
10. Alle Seiten erneut bei Mobile, Tablet und Desktop visuell abnehmen.
