# Humplore Redesign 2026

> Archivstand: Diese Umsetzungsnotiz beschreibt einen abgeschlossenen
> Redesign-Schritt. Für den aktuellen Projektstand gelten `docs/projektstatus.md`
> und `docs/architektur.md`.

## Umfang

Dieses Redesign wurde ausschliesslich in der Kopie `Webseite - Redesign` umgesetzt.
Der Ausgangsordner `Webseite - Codex` wurde nicht veraendert.

Die vorhandenen Routen, Formulare, Handler, Datenbankzugriffe, JavaScript-IDs und
PHP-Funktionsvertraege bleiben bestehen. Das neue Erscheinungsbild wird ueber
`css/humplore-redesign.css` als letzte Stylesheet-Schicht eingebunden.

## Designrichtung

- ruhig, vertrauenswuerdig und redaktionell statt generisch sozial-medial
- tiefes Gruen fuer Sicherheit, Orientierung und Konzentration
- warmes Papierweiss fuer lange, gut lesbare Erfahrungsberichte
- Korall als menschlicher Akzent fuer Fragen, Reaktionen und Einstiege
- Serifenschrift fuer Geschichten und zentrale Aussagen
- klare Sans-Serif-Schrift fuer Navigation, Formulare und Metadaten
- grosszuegige, responsive Karten mit klaren Hierarchien
- Touch-Ziele ab 44 Pixeln, sichtbare Fokuszustaende und reduzierte Bewegung

## Ueberarbeitete Oberflaechen

- Landingpage
- Explore-Feed und Filter
- Suche und Suchergebnisse
- Creator-Profile, Beitraege und Fragen
- Beitragserstellung
- News
- Anmeldung und Registrierung
- eigenstaendige Fragenseite
- statische Informations-, Kontakt- und Rechteseiten

## Verifikation

- PHP-Syntaxpruefung fuer alle 49 aktiven PHP-Dateien erfolgreich
- alle zentralen Routen lokal mit HTTP 200 ausgeliefert
- Design-Stylesheet und Seitenscopes in allen Kernrouten vorhanden
- nicht visuelle Support-, Handler-, Datenbank- und Funktionsdateien stimmen
  bytegenau mit dem Ausgangsprojekt ueberein
