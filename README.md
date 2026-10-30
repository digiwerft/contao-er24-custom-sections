# eRecht24 Rechtstext-Ergänzungen für Contao

Fügt eigene Abschnitte an einer frei wählbaren Stelle in Rechtstexte ein, die mit
[fenepedia/contao-er24-rechtstexte](https://github.com/fenepedia/contao-er24-rechtstexte)
von eRecht24 geladen werden. Die automatische Synchronisation mit eRecht24 bleibt
dabei vollständig erhalten.

Typischer Anwendungsfall: Ein Hotel muss in der Datenschutzerklärung über die Meldung
zur Kurabgabe informieren, der eRecht24-Generator bietet dafür aber keinen Baustein.
Bisher blieb nur, den Text zu kopieren und von Hand zu ergänzen. Ab dann kommen
Aktualisierungen von eRecht24 nicht mehr an.

## Funktionsweise

- Neues Inhaltselement **„eRecht24 Rechtstext-Ergänzung“** (Kategorie „Include-Elemente“).
  Es wird im selben Artikel wie das eRecht24-Element angelegt.
- Im Element wählt man den Rechtstext, den Abschnitt (Auswahlliste mit allen
  Überschriften des Rechtstexts) und die Position *vor* oder *nach* dem Abschnitt.
  „Nach“ bedeutet: hinter dem Abschnitt einschließlich seiner Unterabschnitte.
- Die Ergänzung wird erst beim Ausgeben in das fertige HTML eingefügt, also nach dem
  Abruf und dem Cache des eRecht24-Texts. Pushes von eRecht24 funktionieren deshalb
  weiter wie bisher.
- Der eRecht24-Text bleibt Byte für Byte unverändert, es wird nur an den
  Überschriften-Grenzen eingefügt. Die Überschriftenebene der Ergänzung (h2/h3/h4)
  wird automatisch an den gewählten Abschnitt angepasst.

## Wenn eRecht24 den Text ändert

eRecht24-Überschriften haben keine IDs. Der Abschnitt wird deshalb über den Pfad
seiner Überschriften gefunden, z. B.
`allgemeine-hinweise-und-pflichtinformationen/empfänger-von-personenbezogenen-daten`.

| Änderung durch eRecht24 | Verhalten |
| --- | --- |
| Kapitel werden umnummeriert („3.“ → „4.“) | Kein Problem, Nummern werden ignoriert. |
| Kapitel wird umbenannt, Abschnitt bleibt gleich | Der Abschnitt wird über seinen Namen gefunden, sofern der eindeutig ist. In der Backend-Vorschau erscheint ein Hinweis. |
| Abschnitt wird umbenannt oder entfernt | Die Ergänzung wird **nicht** verworfen, sondern am Ende des Rechtstexts ausgegeben. Im Backend erscheint eine Fehlermeldung am eRecht24-Element und an der Ergänzung, im Frontend ein Eintrag im System-Log. |

## Installation

```bash
composer require digiwerft/contao-er24-custom-sections
```

Danach die Datenbank aktualisieren (`contao:migrate` bzw. Contao Manager / Install-Tool).

Die Auswahlliste der Abschnitte wird aus der zuletzt geladenen Version des Rechtstexts
gebaut. Sie ist gefüllt, sobald das eRecht24-Element einmal angezeigt wurde, im
Frontend oder in der Backend-Vorschau.

## Kompatibilität

- Contao 4.9, 4.13 und 5.x
- PHP 8.1 oder höher
- fenepedia/contao-er24-rechtstexte ^1.0

## Hinweis

Eigene Ergänzungen sind nicht Teil des eRecht24-Texts. Für ihren Inhalt und ihre
Aktualität ist der Betreiber der Website selbst verantwortlich.

## Lizenz

MIT, siehe [LICENSE](LICENSE).
