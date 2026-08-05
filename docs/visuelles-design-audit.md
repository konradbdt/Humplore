# Visueller Design-Audit der aktuellen Humplore-Plattform

Stand: 3. August 2026

## Auftrag

Dieser Audit bewertet ausschließlich das visuelle Design des aktuell
maßgeblichen Codebaums „Webseite - Codex/“.

Die Anwendung selbst, ihre Funktionen und ihr Design wurden für diese Prüfung
nicht verändert. Ersetzt wurde nur die veraltete Analyse der verworfenen Kopie
„Webseite - Redesign/“.

## Bewertungsbasis

Die Seiten wurden lokal im Browser unter folgenden Viewports geprüft:

- Desktop: 1440 × 1000 Pixel
- Mobile: 390 × 844 Pixel

Geprüfte Seiten:

- Startseite
- Login
- Registrierung
- Explore
- Suche mit Ergebnissen
- Creator-Profil
- Beitragserstellung
- News

Die Prüfung umfasst den sichtbaren Ausgangszustand und typische Scrollzustände.
Es wurden keine Inhalte veröffentlicht, Formulare abgesendet oder Daten in der
Datenbank verändert.

## Kurzfazit

Humplore besitzt bereits eine erkennbare Markenbasis: Logo, Olivgrün, helle
Flächen und redaktionelle Karten passen grundsätzlich zu einer vertrauensvollen
Plattform für persönliche Erfahrungen. Die aktuelle Oberfläche wirkt jedoch
nicht wie ein zusammenhängendes Designsystem.

Die größten Schwächen sind die inkonsistente Navigation, zu viele gleichzeitig
sichtbare Informationsbereiche, falsch oder zu aggressiv eingesetzte
Sticky-Elemente, unterschiedliche Schrift- und Farbsysteme sowie eine mobile
Darstellung, in der zentrale Navigation und Inhalte teilweise vollständig
verschwinden. Zusätzlich sind auf der Beitragserstellung sichtbare
Zeichenkodierungsfehler vorhanden.

Es gibt bei 390 Pixel Breite keinen generellen horizontalen Seitenüberlauf.
Viele Komponenten passen technisch in den Viewport. Das responsive Verhalten
ist trotzdem nicht ausreichend, weil Inhalte nur ausgeblendet statt sinnvoll
neu angeordnet werden und mehrere Seiten unterschiedliche mobile
Navigationsmodelle verwenden.

## Größte visuelle Probleme

### 1. Inkonsistente und teilweise fehlende Navigation

- Explore, Profil und News besitzen mobil keine erreichbare Hauptnavigation.
- Die Desktop-Seiten Suche und Beitragserstellung zeigen dagegen eine für
  Mobile gedachte Bottom-Navigation.
- Die Bottom-Navigation verdeckt auf Suche und Beitragserstellung Inhalte am
  unteren Bildschirmrand.
- Seitliche Navigationen werden auf Mobile ausgeblendet, ohne ihre Funktionen
  vollständig in eine andere Navigation zu übertragen.
- Die Kopfzeile ist je nach Seite 72, 85, 91, 125 oder 133 Pixel hoch.
- Öffentliche Seiten, Auth-Seiten und eingeloggte Seiten verwenden
  unterschiedliche Header- und Navigationslogiken.

Auswirkung: Nutzer verlieren die Orientierung und können auf einigen mobilen
Seiten nicht zuverlässig zwischen Explore, Posten, News und Profil wechseln.

### 2. Zu viele Sticky-Elemente und problematische Ebenen

- Auf der mobilen Suche bleiben gleichzeitig der globale Header, die große
  Suchkarte und einzelne Karten-Header sticky.
- Die mobile Suchkarte ist 267 Pixel hoch und bedeckt beim Scrollen einen großen
  Teil des nutzbaren Bildschirms.
- Auf dem Desktop-Profil belegen globaler Header und sticky Profilkopf zusammen
  rund 307 Pixel Höhe.
- Auf Explore sind „mehr lesen“-Bereiche als sticky definiert. Im
  Desktop-Feed liegen sie dadurch teilweise über dem Beitragstext.
- Mehrere eigene Scrollbereiche, besonders die Fragenleiste auf Explore,
  erzeugen Scrollen innerhalb des Scrollens.

Auswirkung: Inhalte werden verdeckt, der sichtbare Lesebereich schrumpft und
die Oberfläche fühlt sich beim Scrollen unruhig an.

### 3. Sehr hohe Informationsdichte auf Explore und Profil

- Explore zeigt gleichzeitig Navigation, Beitragskategorien,
  Themenkategorien, Sortierung, umfangreiche Browse-Karten, den Feed und eine
  Fragenleiste.
- Der eigentliche Feed beginnt auf Desktop erst nach ungefähr 2.000 Pixeln und
  mobil nach ungefähr 4.000 Pixeln Seitenhöhe.
- Die Desktop-Aufteilung verwendet eine 196 Pixel breite linke Seitenleiste,
  einen 813 Pixel breiten Hauptbereich und eine 338 Pixel breite rechte
  Fragenleiste.
- Zahlreiche Labels, Zähler, Pills, Unterüberschriften und Mini-Karten
  konkurrieren gleichzeitig um Aufmerksamkeit.
- Das Profil kombiniert Profilkopf, Navigation, Kategorien, zweispaltigen Feed,
  Fragenformular und beantwortete Fragen in einer Ansicht.

Auswirkung: Es fehlt ein klarer Einstiegspunkt. Nutzer müssen zu viele Bereiche
gleichzeitig erfassen und gelangen nur langsam zu den eigentlichen Geschichten.

### 4. Kein durchgängiges Typografiesystem

Aktuell erscheinen unter anderem:

- Poppins
- Arial
- DM Serif Display
- Lora
- systemabhängige Fallback-Schriften

Die Kombination könnte grundsätzlich funktionieren, wird aber nicht
systematisch eingesetzt:

- Login verwendet überwiegend Arial, Registrierung überwiegend Poppins.
- Überschriften wechseln zwischen Poppins und DM Serif Display.
- Beitragstexte erscheinen in Lora, Metadaten in Poppins und einzelne Controls
  in Arial.
- Suchergebnisse, Profile und Explore verwenden unterschiedliche Gewichtungen
  und Größen für ähnliche Informationen.
- Kleine Metadaten und Aktionsbeschriftungen liegen häufig nur bei ungefähr
  13 bis 14 Pixeln.

Auswirkung: Verwandte Komponenten sehen nicht verwandt aus. Die redaktionelle
Anmutung wirkt zufällig statt bewusst gestaltet.

### 5. Uneinheitliche Farben und schwache Akzentlogik

- Olivgrün ist als Markenfarbe klar erkennbar.
- Die Suche verwendet zusätzlich ein kräftiges Magenta für Button, Fokusrahmen
  und Nutzernamen.
- Aktionsleisten mischen Senfgelb, Magenta, Grau und Hellblau.
- Die Beitragserstellung verwendet dunkles Grün, helle Olivtöne und mehrere
  Grauabstufungen.
- Gesperrte Inhalte verwenden eine pfirsichfarbene Fläche, die nicht aus dem
  restlichen System abgeleitet wirkt.
- Login und Registrierung nutzen unterschiedliche Grüntöne und
  Kontrastlogiken.

Auswirkung: Akzentfarben kommunizieren keine stabilen Bedeutungen. Besonders
Magenta und Hellblau wirken wie Bestandteile eines anderen Produkts.

### 6. Sichtbare Zeichenkodierungsfehler

Auf der Beitragserstellung sind unter anderem fehlerhafte Texte sichtbar:

- „Kurzer, prÃ¤gnanter Titelâ€¦“
- „WorÃ¼ber mÃ¶chtest du schreiben?“
- „WÃ¤hle oder erstelle“
- „ErnÃ¤hrung“
- „2â€“40 Zeichen“

Auch einzelne Seitentitel enthalten fehlerhaft dekodierte Zeichen.

Auswirkung: Die Fehler beschädigen unmittelbar den professionellen Eindruck und
die Vertrauenswürdigkeit der Plattform.

### 7. Unausgewogene Größenverhältnisse und Weißraum

- Die Landingpage nutzt eine sehr hohe Hero-Fläche mit wenig Inhalt.
- Die drei Landingpage-Karten sind rund 400 Pixel hoch, enthalten aber jeweils
  nur eine kurze Überschrift.
- Mobil wächst der Feature-Bereich dadurch auf ungefähr 1.360 Pixel Höhe.
- Die News-Seite zeigt auf Desktop eine 900 Pixel breite Coming-Soon-Karte und
  darunter fast ausschließlich leeren Raum.
- In der Desktop-Beitragserstellung steht rechts unter dem Upload-Bereich eine
  große ungenutzte Fläche, während links viele Formularfelder gestapelt sind.
- Der mobile Profilkopf benötigt zusammen mit Banner und Statistikbereich rund
  1.000 Pixel, bevor der erste Beitrag beginnt.

Auswirkung: Manche Seiten wirken leer und unfertig, andere gleichzeitig zu
dicht. Ein gemeinsamer Rhythmus fehlt.

### 8. Zu viele unterschiedliche Kartenvarianten

- Landingpage, Suche, Explore, Profil, News und Beitragserstellung verwenden
  jeweils eigene Kartenlogiken.
- Radius, Rahmen, Schatten, Innenabstand und Hintergrundfarbe variieren.
- Auf Explore und in der Beitragserstellung entstehen mehrere Ebenen aus Karte
  in Karte in Karte.
- Mini-Beiträge innerhalb von Browse-Karten sehen anders aus als reguläre
  Beiträge und Suchergebnis-Karten.
- Profilkarten und Explore-Karten zeigen ähnliche Inhalte mit unterschiedlichen
  Headern und Aktionsleisten.

Auswirkung: Die Oberfläche wirkt aus einzelnen Seitenentwürfen zusammengesetzt,
nicht aus wiederverwendbaren Komponenten.

### 9. Zu kleine oder uneindeutige Bedienelemente

- Login-Felder und Login-Button sind nur etwa 39 Pixel hoch.
- Registrierungsfelder sind etwa 42 Pixel hoch.
- Die Creator-Checkbox ist lediglich 13 × 13 Pixel groß.
- Mehrere Pfeile, Punkte und Buchstaben dienen als Ersatz für Icons.
- Auf mobilen Beitragskarten verschwinden Aktionsbeschriftungen; übrig bleiben
  teilweise nur kleine Symbole und Zähler.
- Abgekürzte Sidebar-Labels wie „Allgeme...“, „Gesun...“ oder „Schule &...“
  sind ohne weiteren Kontext schwer erfassbar.

Auswirkung: Touch-Bedienung, schnelle Erfassbarkeit und Barrierefreiheit leiden.

### 10. Mobile Inhalte werden ausgeblendet statt neu strukturiert

- Die Explore-Seitenleisten verschwinden vollständig.
- Auf dem Profil verschwinden Navigation, Kategorien, Fragenformular und
  beantwortete Fragen aus der Hauptansicht.
- Auf News verschwindet die Seitenleiste ersatzlos.
- In der mobilen Beitragserstellung ist kein sichtbarer Upload-Bereich
  vorhanden.
- Nur Suche und Beitragserstellung zeigen eine Bottom-Navigation.

Auswirkung: Mobile Nutzer erhalten je nach Seite einen anderen und teilweise
unvollständigen Funktionsumfang.

## Detaillierte Bewertung nach Gestaltungskriterium

### Visuelle Hierarchie

Positiv:

- Große Seitentitel und Kartenüberschriften sind grundsätzlich gut erkennbar.
- Nutzername, Thema und Kategorie werden häufig klar gruppiert.
- Suchergebniszahlen sind an mehreren Stellen sichtbar.

Problematisch:

- Explore besitzt zu viele gleich stark betonte Überschriften und Karten.
- Pills, Zähler und „Mehr anzeigen“-Buttons konkurrieren mit den eigentlichen
  Inhalten.
- Auf Profilen sind Profilkopf, Feed und Fragenbereich gleichzeitig dominant.
- Landingpage und News verwenden sehr große Flächen für sehr wenig Information.

### Seitenaufbau

- Die Desktop-Aufteilung von Explore und Profil ist zu komplex und zu starr.
- Der zentrale Lesebereich ist im Verhältnis zur Gesamtbreite zu klein.
- Die Suche ist mit einem zentrierten Maximalbereich von ungefähr 1.120 Pixeln
  grundsätzlich verständlicher aufgebaut.
- Die Beitragserstellung besitzt auf Desktop eine sinnvolle Zweiteilung, ist
  aber durch die leere rechte Spalte unausgewogen.
- Mobile Seiten werden überwiegend einspaltig dargestellt und erzeugen keinen
  generellen horizontalen Überlauf.

### Abstände

- Innerhalb einzelner Karten sind Abstände oft sauber und wiederholbar.
- Seitenübergreifend existieren jedoch zu viele Werte für Außenabstände,
  Kartenabstände und vertikale Zwischenräume.
- Landingpage, News und Profilkopf verwenden übergroße vertikale Flächen.
- Explore verwendet dagegen sehr kleine Abstände zwischen vielen Informationen.
- Die feste Bottom-Navigation besitzt keinen zuverlässig reservierten
  Inhaltsabstand am Seitenende.

### Größenverhältnisse

- Logo und globale Suche sind auf eingeloggten Desktop-Seiten gut proportioniert.
- Die linke Explore-Seitenleiste ist mit 196 Pixeln zu schmal für lange
  Kategorienamen.
- Die rechte Fragenleiste ist breit, aber durch viele kleine Karten und eigenen
  Scrollbereich trotzdem gequetscht.
- Karten der Desktop-Suche sind mit ungefähr 535 Pixeln gut lesbar, ihre stark
  unterschiedlichen Höhen erzeugen jedoch ein unruhiges Raster.
- Mobile Kartenbreiten zwischen 347 und 351 Pixeln passen gut in den Viewport.

### Typografie

- Poppins eignet sich gut für Navigation, Controls und Metadaten.
- Eine Serifenschrift kann die persönlichen Geschichten glaubwürdig und
  redaktionell wirken lassen.
- Es sollte nur eine Serifenschrift und eine Sans-Serif-Schrift geben.
- Arial sollte vollständig aus sichtbaren Komponenten entfernt werden.
- Fließtext sollte mobil mindestens 16 Pixel groß sein.
- Metadaten sollten nicht unter 14 Pixel fallen und einen ausreichenden
  Farbkontrast erhalten.

### Farben und Kontraste

- Das Olivgrün passt zur gewünschten ruhigen, vertrauensvollen Marke.
- Weiß und warmes Hellgrau unterstützen lange Inhalte.
- Olive-on-olive-Kombinationen auf Login und Registrierung benötigen eine
  systematische Kontrastprüfung.
- Magenta, Hellblau, Senfgelb und Pfirsich sollten entweder mit klarer Bedeutung
  in ein Tokensystem aufgenommen oder entfernt werden.
- Fokus-, Hover-, Aktiv-, Fehler- und Erfolgstöne sind nicht konsistent
  definiert.

### Karten

- Profil- und Feedkarten sind mobil grundsätzlich gut erfassbar.
- Runde Ecken und feine Rahmen passen zur Marke.
- Es fehlen gemeinsame Regeln für Standardkarte, kompakte Karte, Beitragskarte,
  Profilkarte und Statuskarte.
- Verschachtelte Karten sollten reduziert werden.
- Schatten sollten zurückhaltender und konsistenter eingesetzt werden.

### Buttons

- Primäre Buttons sind grundsätzlich als gefüllte Flächen erkennbar.
- Beschriftungen wechseln zwischen Deutsch und Englisch, beispielsweise
  „Share!“ statt „Veröffentlichen“.
- Suchbuttons unterscheiden sich je nach Seite in Farbe, Form und Typografie.
- „Mehr anzeigen“, „Folgen“, „Profil teilen“ und Aktionsbuttons besitzen keine
  gemeinsame Variantenlogik.
- Alle interaktiven Ziele sollten mindestens 44 × 44 Pixel erreichen.

### Formulare

- Login und Registrierung sind einfach aufgebaut und visuell verständlich.
- Die Beitragserstellung zeigt Labels, Zeichenzähler und optionale Bereiche.
- Login und Registrierung wirken dennoch wie zwei verschiedene Produkte.
- Checkboxen sind zu klein.
- Die Desktop-Beitragserstellung ist links überladen und rechts leer.
- Die mobile Beitragserstellung zeigt keinen sichtbaren Bildupload.
- Die Bottom-Navigation verdeckt mobile Formularbereiche.
- Sichtbare Encoding-Fehler machen gerade die Beitragserstellung unfertig.

### Tabellen

Auf den geprüften Kernseiten werden keine sichtbaren Datentabellen verwendet.
Eine belastbare visuelle Tabellenbewertung ist daher nicht möglich. Für spätere
Moderations- oder Verwaltungsansichten sollte frühzeitig ein responsives
Tabellenmuster mit Spaltenpriorisierung, horizontalem Scrollen oder mobiler
Kartenalternative definiert werden.

### Navigation

- Das Humplore-Logo ist ein guter wiederkehrender Orientierungspunkt.
- Die Navigation ist das größte responsive Problem der Plattform.
- Es fehlt ein gemeinsamer App-Header für alle eingeloggten Seiten.
- Es fehlt eine verbindliche Regel, wann Sidebar, Header-Navigation oder
  Bottom-Navigation verwendet wird.
- Mobile Bottom-Navigation muss auf allen Kernseiten identisch vorhanden sein.
- Desktop darf keine mobile Bottom-Navigation anzeigen.

### Icons

- Logo und einzelne Aktionssymbole sind grundsätzlich verständlich.
- Navigation verwendet uneinheitlich Punkte, Pluszeichen, Buchstaben, Hashtags
  und Pfeile.
- Aktionsicons besitzen unterschiedliche Farben und Strichstärken.
- Icons sollten aus einer gemeinsamen Bibliothek stammen, dieselben optischen
  Größen verwenden und standardmäßig die Textfarbe übernehmen.

### Weißraum

- Auth-Seiten besitzen ausreichend Ruhe um die Formulare.
- Landingpage und News verwenden zu viel leeren Raum ohne inhaltliche Funktion.
- Explore und Profil besitzen gleichzeitig zu wenig lokalen Weißraum zwischen
  Informationsgruppen.
- Guter Weißraum sollte Abschnitte hierarchisieren, nicht lediglich große
  ungenutzte Flächen erzeugen.

### Informationsdichte

- Explore ist deutlich zu dicht und beginnt zu spät mit dem Feed.
- Profil ist auf Desktop überladen und auf Mobile übermäßig lang.
- Suche besitzt eine gute grundlegende Gliederung, wird aber durch Sticky-Logik
  und variable Kartenhöhen unruhig.
- Login und Registrierung haben eine passende Informationsdichte.
- News ist inhaltlich zu leer für die große visuelle Inszenierung.

### Konsistenz zwischen den Seiten

Inkonsistent sind insbesondere:

- Headerhöhen
- Navigation
- Schriftfamilien
- Akzentfarben
- Buttonvarianten
- Kartenstile
- Abstände
- Sticky-Verhalten
- mobile Ersatznavigation
- Sprache der Beschriftungen

Die Plattform besitzt wiederkehrende Einzelmotive, aber noch kein belastbares
Designsystem.

### Responsive Gestaltung

Stärken:

- Kein genereller horizontaler Überlauf bei 390 Pixel Breite.
- Feed- und Suchkarten werden einspaltig.
- Formulare passen grundsätzlich in den Viewport.
- Logo und mobile Suchzeile bleiben lesbar.

Schwächen:

- Navigation fehlt auf mehreren Seiten.
- Inhalte und Funktionen werden ausgeblendet.
- Sticky-Flächen nehmen zu viel Höhe ein.
- Explore wird mehr als 10.000 Pixel lang.
- Profil benötigt ungefähr 1.000 Pixel bis zum ersten Beitrag.
- Bottom-Navigation verdeckt Inhalte und erscheint auf falschen Breakpoints.
- Touch-Ziele sind teilweise zu klein.

## Seitenbezogene Befunde

### Startseite

Desktop:

- Klarer, ruhiger Einstieg und gut lesbare zentrale Botschaft.
- Der Hero ist höher als der verbleibende Viewport und enthält sehr viel leere
  Fläche.
- Feature-Karten wirken unfertig, da ihnen Icons, Beschreibung und Aktion fehlen.

Mobile:

- Header, Titel, Text und CTA passen sauber in den Viewport.
- Jede Feature-Karte benötigt ungefähr 400 Pixel Höhe für nur eine Überschrift.
- Der Weg vom Einstieg zum Footer ist unnötig lang.

### Login

Desktop:

- Einfaches und verständliches Formular.
- Die rein olivfarbene Fläche wirkt monoton.
- Arial und die hellere Karte passen nicht zur Registrierung.
- Logo, Zurück-Navigation und Markenbotschaft fehlen.

Mobile:

- Formular passt ohne Scrollen in den Viewport.
- Karte liegt ohne seitlichen Außenabstand direkt an den Bildschirmkanten.
- Felder und Button sind mit etwa 39 Pixeln zu niedrig.

### Registrierung

Desktop:

- Besser an die Markenwelt angepasst als der Login.
- Creator-Option ist visuell sehr klein.
- Hauptthema erscheint erst bedingt und besitzt keine vorbereitete räumliche
  Reserve.

Mobile:

- Verständliche Einspaltenstruktur.
- Ebenfalls keine seitliche Luft um die Hauptkarte.
- Checkbox und Touch-Ziele sind zu klein.

### Explore

Desktop:

- Klare Markenfarben und viele nützliche Inhalte.
- Dreispaltige Struktur ist überladen.
- Sidebar-Beschriftungen werden abgeschnitten.
- Rechte Fragenleiste besitzt einen eigenen Scrollbereich.
- Feed beginnt erst nach den sehr umfangreichen Browse-Bereichen.
- „Mehr lesen“ kann Beitragstext überlagern.

Mobile:

- Karten passen ohne horizontalen Überlauf.
- Browse-Bereich ist zu lang und zu stark verschachtelt.
- Feed beginnt erst nach ungefähr 4.000 Pixeln.
- Hauptnavigation fehlt vollständig.
- Die Seite erreicht mit den vorhandenen Daten mehr als 10.000 Pixel Höhe.

### Suche

Desktop:

- Ergebniszahlen, Profil- und Beitragsbereiche sind klar getrennt.
- Magenta bricht die Markenpalette.
- Zweispaltige Karten mit sehr unterschiedlichen Höhen wirken unruhig.
- Mobile Bottom-Navigation erscheint fälschlich auf Desktop.

Mobile:

- Gute einspaltige Grundstruktur und klare Ergebniszahlen.
- Die 267 Pixel hohe Suchkarte bleibt sticky und verdeckt beim Scrollen einen
  großen Teil des Inhalts.
- Karten-Header sind ebenfalls sticky und konkurrieren um dieselbe Ebene.
- Bottom-Navigation verdeckt den unteren Kartenbereich.

### Profil

Desktop:

- Thema, Nutzername, Bio und Kennzahlen sind grundsätzlich verständlich.
- Globaler Header und Profilkopf benötigen zusammen zu viel feste Höhe.
- Linke Navigation, zweispaltiger Feed und rechte Fragenleiste überladen die
  Ansicht.
- Farben der Beitragsaktionen sind uneinheitlich.
- Kategorienamen werden in der schmalen Sidebar abgeschnitten.

Mobile:

- Profilinformationen und Beitragskarten sind einzeln gut lesbar.
- Zwischen Header und Profil liegt ein großer leerer Olivbereich.
- Profilkopf und Kennzahlen benötigen ungefähr 1.000 Pixel vor dem Feed.
- Fragenbereich, Kategorien und Hauptnavigation fehlen.
- Es gibt keine konsistente Bottom-Navigation.

### Beitragserstellung

Desktop:

- Labels, Zähler und Uploadbereich sind grundsätzlich sinnvoll gegliedert.
- Linke Formularspalte ist dicht, die rechte Spalte unter dem Upload fast leer.
- Viele verschachtelte Karten erzeugen visuelle Unruhe.
- Bottom-Navigation erscheint fälschlich auf Desktop.
- Zeichenkodierungsfehler sind direkt sichtbar.

Mobile:

- Formular wird korrekt einspaltig.
- Bildupload ist nicht sichtbar.
- Bottom-Navigation liegt über den unteren Formularbereichen.
- Kleine Badges und lange Labels werden gequetscht.
- Zeichenkodierungsfehler sind besonders auffällig.

### News

Desktop:

- Klarer Leerzustand und verständliche Botschaft.
- Sidebar mit funktionslosen Kategorien lenkt unnötig ab.
- Große Karte und sehr viel ungenutzter Raum lassen die Seite unfertig wirken.
- Magentafarbener Ladeindikator passt nicht zur Palette.

Mobile:

- Leerzustand passt sauber in den Viewport.
- Seitennavigation verschwindet vollständig.
- Die sekundäre „Coming Soon“-Überschrift ist nicht mehr sichtbar.
- Unter der Karte bleibt ein großer leerer Bereich.

## Gestalterische Stärken

- Wiedererkennbares Humplore-Logo.
- Ruhige olivgrüne Markenbasis.
- Helle Kartenflächen eignen sich gut für längere Geschichten.
- Grundidee einer Kombination aus sachlicher Sans-Serif und redaktioneller
  Serifenschrift.
- Klare Nutzer-, Themen- und Kategorie-Pills.
- Gut erkennbare Profilbilder und Creator-Zuordnung.
- Ergebniszahlen der Suche sind verständlich platziert.
- Mobile Karten passen grundsätzlich in den Viewport.
- Reguläre Feedkarten besitzen eine nachvollziehbare Reihenfolge aus Autor,
  Kategorie, Titel, Text und Aktionen.
- Formulare verwenden sichtbare Labels statt nur Platzhalter.
- Runde Ecken und zurückhaltende Kartenflächen passen zum freundlichen,
  sicheren Produktcharakter.

## Empfohlene neue Designrichtung

### Leitbild

Humplore sollte wie eine ruhige, vertrauenswürdige und redaktionelle
Wissensplattform für persönliche Erfahrungen wirken – nicht wie ein dichtes
Social-Media-Dashboard.

Zentrale Eigenschaften:

- menschlich
- sicher
- ruhig
- glaubwürdig
- redaktionell
- zugänglich
- strukturiert

### Typografie

- Poppins für Navigation, Buttons, Formulare und Metadaten.
- Eine einzige Serifenschrift für Seitentitel, Beitragstitel und längere
  Geschichten; empfohlen wird Lora oder DM Serif Display, nicht beide parallel.
- Keine sichtbaren Arial-Ausnahmen.
- Fließtext: mindestens 16 Pixel, 1,55 bis 1,7 Zeilenhöhe.
- Metadaten: mindestens 14 Pixel.
- Klar definierte Stufen für Seitentitel, Abschnittstitel, Kartentitel,
  Fließtext und Metadaten.

### Farbwelt

- Primär: tiefes Humplore-Oliv.
- Sekundär: ein helleres Salbei für Flächen und aktive Zustände.
- Hintergrund: warmes Weiß oder sehr helles Grau.
- Text: dunkles Anthrazit statt reines Schwarz.
- Ein einziger Akzentton für wichtige Aktionen.
- Semantische Farben nur für Erfolg, Warnung, Fehler und Information.
- Kein willkürliches Magenta, Senfgelb oder Hellblau in Standardaktionen.

### Layout

- Gemeinsame maximale Inhaltsbreite von ungefähr 1.200 Pixeln.
- Lesespalten für Geschichten zwischen 640 und 720 Pixeln.
- Desktop höchstens eine primäre Seitenleiste.
- Rechte Zusatzspalten nur bei ausreichender Breite und mit klarer Priorität.
- Explore soll zuerst eine kompakte Themenauswahl und danach unmittelbar den
  Feed zeigen.
- Profilinhalte mobil über Tabs wie „Beiträge“, „Fragen“ und „Info“ zugänglich
  machen.

### Abstände

Empfohlenes Raster:

- 4 Pixel
- 8 Pixel
- 12 Pixel
- 16 Pixel
- 24 Pixel
- 32 Pixel
- 48 Pixel
- 64 Pixel

Standardwerte:

- Kartenabstand mobil: 12 bis 16 Pixel
- Kartenabstand Desktop: 20 bis 24 Pixel
- Karteninnenabstand mobil: 16 Pixel
- Karteninnenabstand Desktop: 20 bis 24 Pixel
- Abschnittsabstand: 32 bis 48 Pixel

### Karten

- Radius zwischen 12 und 16 Pixeln.
- Feiner Rahmen statt mehrerer starker Schatten.
- Gemeinsamer Innenabstand.
- Keine unnötigen Kartenverschachtelungen.
- Verbindliche Varianten für Standardkarte, Beitragskarte, Profilkarte,
  kompakte Vorschau und Leerzustand.

### Navigation

- Ein gemeinsamer App-Header für alle eingeloggten Seiten.
- Desktop: Logo, Suche und kompakte Hauptnavigation.
- Mobile: Logo und kompakte Suche im Header sowie dieselbe Bottom-Navigation auf
  allen Kernseiten.
- Bottom-Navigation ausschließlich auf Mobile.
- Aktiver Navigationspunkt klar markiert.
- Feste Elemente müssen einen reservierten Inhaltsabstand erhalten.

### Responsive Gestaltung

- Mobile zuerst gestalten.
- Unterhalb von ungefähr 900 Pixeln keine festen Seitenleisten.
- Seitenleisteninhalte in Tabs, Filter-Drawer oder aufklappbare Bereiche
  übertragen.
- Sticky-Suche nach dem Scrollen auf eine einzelne kompakte Zeile reduzieren.
- Nur ein dominantes Sticky-Element pro Bildschirmkante.
- Alle Touch-Ziele mindestens 44 × 44 Pixel.
- Keine Funktion darf auf Mobile nur durch „display: none“ verschwinden.

## Elemente, die beibehalten werden sollten

- Humplore-Logo.
- Olivgrüne Markenrichtung.
- Helle, ruhige Kartenflächen.
- Grundidee redaktioneller Beitragstypografie.
- Creator-Zuordnung mit Profilbild und Nutzername.
- Themen- und Kategorie-Pills.
- Ergebniszahlen der Suche.
- Profilinformationen aus Thema, Bio, Ort, Sprache und Kennzahlen.
- Beitragsaktionen Lernen, Kommentieren, Merken und Teilen.
- Uploadfläche, Labels und Zeichenzähler der Beitragserstellung.
- Trennung zwischen Profilen und Beiträgen in der Suche.
- Einspaltige mobile Beitragskarten.

## Elemente, die neu gestaltet werden sollten

- Gesamter App-Header.
- Einheitliche Desktop- und Mobile-Navigation.
- Bottom-Navigation und Breakpoints.
- Explore-Informationsarchitektur.
- Themen- und Kategorieübersicht.
- Seitliche Fragenleiste.
- Sticky-Verhalten aller Seiten.
- Landingpage-Featurekarten.
- Typografiesystem.
- Farbtokens und Akzentfarben.
- Buttonvarianten.
- Formulare und Touch-Ziele.
- Kartenvarianten und Schatten.
- Beitragsaktionsleisten.
- Profilkopf und Profilnavigation.
- Mobile Profilbereiche für Fragen und Kategorien.
- Suchkarte und Suchergebnisraster.
- Desktop- und Mobile-Aufteilung der Beitragserstellung.
- Mobile Uploaddarstellung.
- News-Leerzustand.
- Login- und Registrierungsseiten als gemeinsames Auth-System.
- Vollständige Icon-Sprache.
- Sämtliche sichtbaren Zeichenkodierungsfehler.

## Empfohlene Priorisierung

### Priorität 1: Kritische responsive und sichtbare Fehler

1. Zeichenkodierungsfehler beseitigen.
2. Mobile Navigation auf allen Kernseiten herstellen.
3. Desktop-Bottom-Navigation entfernen.
4. Überlagernde Sticky-Elemente korrigieren.
5. Bottom-Navigation mit sicherem Inhaltsabstand versehen.
6. Mobile Uploaddarstellung wieder sichtbar machen.

### Priorität 2: Gemeinsames Designsystem

1. Farben, Typografie, Abstände und Radien als Tokens definieren.
2. App-Header und Navigation vereinheitlichen.
3. Buttons, Formulare, Karten und Icons standardisieren.
4. Touch-Ziele und Kontraste prüfen.

### Priorität 3: Seiten neu strukturieren

1. Explore verkürzen und Feed früher zeigen.
2. Profil in klar priorisierte Bereiche oder Tabs gliedern.
3. Suche ohne große Sticky-Fläche gestalten.
4. Beitragserstellung räumlich ausgleichen.
5. Landingpage und News mit sinnvollerem Informationsgehalt versehen.

### Priorität 4: Visuelle Abnahme

Nach der Überarbeitung sollten mindestens folgende Viewports geprüft werden:

- 390 × 844 Pixel
- 768 × 1024 Pixel
- 1024 × 768 Pixel
- 1440 × 1000 Pixel
- 1920 × 1080 Pixel

Die Abnahme sollte pro Seite Überlappungen, abgeschnittene Texte, Sticky-Ebenen,
Touch-Ziele, Kontraste, Tastaturfokus und den unteren Sicherheitsabstand prüfen.
