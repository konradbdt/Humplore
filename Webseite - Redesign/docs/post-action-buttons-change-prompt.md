# Prompt: Post Action Buttons Als Dokumentierte Aenderung Aufnehmen

Nutze diesen Prompt, wenn die Aenderung mit Spec-Kit und OpenSpec weitergefuehrt oder erneut an einen Coding-Agenten gegeben werden soll.

```text
Bitte nimm die Post-Action-Buttons als eigene dokumentierte Humplore-Aenderung auf und arbeite nach der vorhandenen Spec-Kit- und OpenSpec-Struktur.

Kontext:
- Projekt: Humplore, brownfield PHP/SQLite Website.
- Bestehende Doku liegt in `.specify/specs/...` und `openspec/changes/...`.
- Aktuelle Post-Buttons unter Beitraegen: `Wissenswert` mit Gluehbirne, `Kommentieren` mit Sprachblase, `Teilen` mit Molekuel-Icon.

Gewuenschte Aenderung:
- `Wissenswert` wird zu `Neues gelernt!`.
- `Neues gelernt!` behaelt die Gluehbirne und die vorhandene Reaktions-/Zaehllogik.
- `Neues gelernt!` behaelt auch das bisherige Verhalten bei kostenpflichtigen/gesperrten Beitraegen.
- Die sichtbare Kommentar-Beschriftung soll `Kommentieren` sein, nicht `Kommentar`.
- `Kommentieren` nutzt eine Sprachblase, behaelt den bestehenden Kommentar-Zaehler und fuehrt zur vorhandenen Kommentaroberflaeche.
- Beitraege bekommen in dieser Aenderung keinen separaten Frage-Modus.
- Neu: `Merken` als persistente Merken-Aktion fuer Beitraege.
- `Merken` nutzt ein Bookmark/Lesezeichen-Icon, kein Disketten-/Datei-Speichern-Icon.
- `Merken` braucht sichtbaren markiert/nicht-markiert Zustand und darf keine doppelten Merken-Eintraege pro User/Post erzeugen.
- `Merken` gilt nur fuer eingeloggte Nutzer und bleibt privat ohne oeffentlichen Zaehler.
- `Merken` soll per Ajax/Fetch toggeln, passend zur bestehenden `Wissenswert`/Like-Logik.
- `Merken` bekommt den eigenen englisch benannten Handler `save_post_handler.php` und wird nicht in `like_handler.php` eingebaut.
- `save_post_handler.php` muss CSRF validieren; der Fetch muss den bestehenden CSRF-Token mitsenden.
- Nicht eingeloggte Aufrufe an `save_post_handler.php` geben JSON mit HTTP 401 zurueck, kein Login-Redirect.
- Erfolgreiche Antworten von `save_post_handler.php` sind minimal: `{ "post_id": 123, "saved": true }` oder `{ "post_id": 123, "saved": false }`; keine HTML-Fragmente, keine Counts.
- Nach erfolgreichem Toggle muessen alle gerenderten `Merken`-Buttons mit derselben `post_id` synchronisiert werden.
- `Merken` zeigt Erfolg nur ueber aktiven/inaktiven Bookmark-Zustand, nicht per Success-Toast.
- Fuer `Merken` soll die Tabelle als SQL dokumentiert und zusaetzlich defensiv ueber das bestehende `humplore_ensure_database_schema()`-Muster mit `CREATE TABLE IF NOT EXISTS` sichergestellt werden.
- Der technische Tabellenname fuer `Merken` ist `SavedPosts`.
- `SavedPosts` bleibt minimal: `user_id`, `post_id`, `created_at`; keine Collections/Ordner/Kontextfelder in dieser Aenderung.
- `Merken` darf bei sichtbaren kostenpflichtigen/gesperrten Postkarten funktionieren, darf aber keinen Zugriff auf gesperrte Inhalte geben.
- `Teilen` bleibt erhalten und behaelt das Molekuel-Icon sowie die vorhandene Share-Logik.
- Donation wird nur als spaetere Erweiterung dokumentiert, aber in dieser ersten Aenderung nicht als deaktivierter oder funktionsloser Button angezeigt.
- Donation soll spaeter pro Beitrag gedacht werden, nicht nur pauschal pro Creator.
- Reihenfolge der ersten Umsetzung: `Neues gelernt!`, `Kommentieren`, `Merken`, `Teilen`; spaeter optional `Donation`.
- Auf Mobile sollen nur Icons sichtbar sein; die Buttons brauchen trotzdem klare Accessible Labels.
- Auf Mobile bleiben Counts fuer `Neues gelernt!` und `Kommentieren` sichtbar.
- Auf Mobile haben `Merken` und `Teilen` keine Counts.

Bitte dokumentiere die Aenderung so wie die anderen:
1. Spec-Kit: Lege oder aktualisiere ein Feature unter `.specify/specs/004-post-action-buttons/` mit `spec.md`, `plan.md` und `tasks.md`.
2. OpenSpec: Lege oder aktualisiere einen Change unter `openspec/changes/post-action-buttons/` mit `proposal.md`, `design.md`, `tasks.md` und einer Delta-Spec unter `specs/engagement/spec.md`.
3. Requirements muessen SHALL/MUST/SHOULD/MAY verwenden.
4. Szenarien muessen GIVEN/WHEN/THEN nutzen.
5. Tasks muessen unabhaengig pruefbar sein.
6. Implementiere Donation noch nicht; dokumentiere sie nur als Future Extension.
7. Achte darauf, dass die UI auf Explore/Search-Postkarten und Profil-Postkarten konsistent bleibt.
8. Die Button-Leiste soll auch in Post-Modals identisch bleiben, falls dort Post-Actions gerendert werden.

Bevor du Code implementierst, pruefe die vorhandenen Partial-Dateien und Handler:
- `Webseite - Codex/app/views/partials/platform-post-card.php`
- `Webseite - Codex/app/views/partials/profile-post-card.php`
- vorhandene Like-/Comment-/Share-Handler
- vorhandene Datenbanktabellen oder Helper fuer gespeicherte Beitraege

Ergebnis:
- Aktualisierte Spec-Kit- und OpenSpec-Artefakte.
- Danach eine kurze Zusammenfassung offener Produktentscheidungen, besonders: Wo sollen gespeicherte Beitraege spaeter sichtbar sein?
```
