# Schedule_XH

Schedule_XH ermöglicht die Koordination von Treffen für Mitglieder
(durch [Register_XH](https://github.com/cmb69/register_xh)
oder [Memberpages](https://github.com/cmsimple-xh/memberpages) registriert)
auf Ihrer CMSimple_XH Website. Es ist ähnlich zum
Doodle-Service, da aber die Termine keine semantische Bedeutung für Schedule_XH
haben, kann es ebenso für andere Abstimmungen verwendet werden.

- [Voraussetzungen](#voraussetzungen)
- [Download](#download)
- [Installation](#installation)
- [Einstellungen](#einstellungen)
- [Verwendung](#verwendung)
- [Fehlerbehebung](#fehlerbehebung)
- [Lizenz](#lizenz)
- [Danksagung](#danksagung)

## Voraussetzungen

Schedule_XH ist ein Plugin für CMSimple_XH.
Es benötigt CMSimple_XH ≥ 1.7.0 und PHP ≥ 7.1.0.

## Download

Das [aktuelle Release](https://github.com/cmb69/schedule_xh/releases/latest)
kann von Github herunter geladen werden.

## Installation

Die Installation erfolgt wie bei vielen anderen CMSimple_XH-Plugins
auch. Im
[CMSimple\_XH-Wiki](https://wiki.cmsimple-xh.org/doku.php/de:installation#plugins)
finden Sie weitere Details.

1. **Sichern Sie die Daten auf Ihrem Server.**
1. Entpacken Sie die ZIP-Datei auf Ihrem Rechner.
1. Laden Sie das ganze Verzeichnis `schedule/` auf Ihren Server in das
   Plugin-Verzeichnis von CMSimple_XH  hoch.
1. Machen Sie die Unterverzeichnisse `config/`, `css/`, `languages/` und den
   Datenordner des Plugins beschreibbar.
1. Gehen Sie zu `Schedule` im Administrationsbereich, um zu prüfen, ob alle
   Voraussetzungen erfüllt sind.

Beachten Sie, dass die Datendateien von Schedule_XH durch direkten Aufruf
ihrer URL eingesehen werden können. Eine `.htaccess` Datei um das zu verhindern
befindet sich bereits im voreingestellten Datenordner. Für andere Server oder
für angepasste Datenordner müssen Sie selbst darauf achten, dass diese Daten vor
nicht autorisiertem Zugriff geschützt sind.

## Einstellungen

Die Plugin-Konfiguration erfolgt wie bei vielen anderen CMSimple_XH-Plugins
auch im Administrationsbereich der Website. Wählen Sie `Plugins` → `Schedule`.

Sie können die Voreinstellungen von Schedule_XH unter `Konfiguration` ändern.
Hinweise zu den Optionen werden beim Überfahren der Hilfe-Icons mit der Maus
angezeigt.

Die Lokalisierung wird unter `Sprache` vorgenommen. Sie können die
Sprachtexte in Ihre eigene Sprache übersetzen, falls keine entsprechende
Sprachdatei zur Verfügung steht, oder diese Ihren Wünschen gemäß anpassen.

Das Aussehen von Schedule_XH kann unter `Stylesheet` angepasst werden.

## Verwendung

Um eine Planung/Abstimmung auf einer CMSimple_XH-Seite zu ermöglichen, verwenden
Sie den folgenden Plugin-Aufruf:

    {{{schedule('%NAME%', %GESAMTERGEBNIS%, %SCHREIBGESCHÜTZT%, %MEHRFACH%, '%OPTION_1%', '%OPTION_2%', '%OPTION_N%')}}}

Die Parameter haben folgende Bedeutung:

- `%NAME%`:
  Der Name des Ereignisses oder einer sonstigen Abstimmung. Dieser wird als
  Basisname der CSV-Datei verwendet, in der die Ergebnisse gespeichert werden, und
  darf daher nur Kleinbuchstaben (`a`-`z`), Ziffern (`0`-`9`) und Minus-Zeichen (`-`)
  enthalten.
- `%GESAMTERGEBNIS%`:
  Ob das Gesamtergebnis angezeigt werden soll: `true` (bedeutet ja)
  oder `false` (bedeutet nein). Es ist wichtig diesen Parameter ohne
  Anführungszeichen zu notieren. Wenn Sie die Voreinstellung aus der
  Plugin-Konfiguration verwenden wollen, können Sie diesen Parameter auslassen,
  wenn Sie ebenfalls den `%SCHREIBGESCHÜTZT%` und den `%MEHRFACH%` Parameter
  auslassen.
- `%SCHREIBGESCHÜTZT%`:
  Ob der Planer schreibgeschützt sein soll: `true` (bedeutet ja) oder
  `false` (bedeutet nein). Es ist wichtig diesen Parameter ohne
  Anführungszeichen zu notieren. Wenn Sie die Voreinstellung aus der
  Plugin-Konfiguration verwenden wollen, können Sie diesen Parameter auslassen,
  wenn Sie ebenfalls den `%MEHRFACH%` Parameter auslassen.
- `%MEHRFACH%`:
  Ob mehrere Optionen für einen einzigen Planer ausgewählt werden dürfen:
  `true` (bedeutet ja) oder `false` (bedeutet nein). Es ist wichtig
  diesen Parameter ohne Anführungszeichen zu notieren. Wenn Sie die Voreinstellung
  aus der Plugin-Konfiguration verwenden wollen, dann lassen Sie diesen Parameter
  einfach weg.
- `%OPTION_X%`:
  Der Name der Option, der als Überschrift der entsprechenden Tabellenspalte
  angezeigt wird. Dies ist normalerweise ein Datum, aber da dieser Name keine
  semantische Bedeutung für Schedule_XH hat, kann es eine beliebige Zeichenkette
  sein. Grundsätzlich können Sie so viele Option angegeben wie Sie möchten.

Es ist nicht erforderlich den Zugriff auf diese Seite auf Mitglieder zu
beschränken; wenn Besucher Ihrer Website nicht angemeldet sind, werden sie eben
nur die aktuellen Ergebnisse sehen, ohne jedoch abstimmen zu können. Daher
können Sie den Planer auch im Template platzieren:

    <?php echo schedule('%NAME%', %GESAMTERGEBNIS%, %SCHREIBGESCHÜTZT%, %MEHRFACH%, '%OPTION_1%', '%OPTION_2%', '%OPTION_N%')?>

Wenn sie allerdings die Abstimmung auf eine bestimmte Benutzergruppe bzw.
Zugriffsebene beschränken wollen, müssen Sie den Planer auf einer Seite
unterbringen, die nur Mitgliedern mit entsprechender Berechtigung zugänglich
ist.

### Beispiele

Um eine Weihnachtsfeier für die Belegschaft zu terminieren:

    {{{schedule('Weihnachten', '18.12.', '19.12.', '22.12.')}}}

![Screenshot des Abstimmungs-Widgets](https://raw.githubusercontent.com/cmb69/schedule_xh/master/help/christmas_de.gif)

Nachdem die Abstimmung für die Weihnachtsfeier beendet ist, möchten Sie den
Planer vielleicht weiterhin angezeigt lassen, so dass jeder das Ergebnis sehen
kann. In diesem Fall sollten Sie den Schreibschutz aktivieren. Um den
`%SCHREIBGESCHÜTZT%` Parameter zu verwenden, müssen Sie ebenfalls den
`%GESAMTERGEBNIS%` Parameter angeben:

    {{{schedule('Weihnachten', false, true, '18.12.', '19.12.', '22.12.')}}}

Für eine Abstimmung über die Farbe der neuen Mannschaftstrikots, bei der das
Gesamtergebnis unabhängig von der Einstellung der entsprechenden
Konfigurationsoption unterhalb der Tabelle angezeigt wird:

    {{{schedule('Trikots', true, 'rot', 'grün', 'blau')}}}

### Hinweise

- Sie können so viele Planer haben wie Sie wünschen; geben Sie diesen einfach
  unterschiedliche Namen.
- Sie können den selben Planer auf verschiedenen Seiten anzeigen; verwenden
  Sie einfach den selben Namen.
- Die Mitglieder können jederzeit erneut abstimmen.
- Sie können die Optionen ändern, auch wenn bereits einige Mitglieder
  abgestimmt haben. Da die Stimmen den Optionsnamen zugewiesen werden (nicht nach
  Position), sollte das vernünftig funktionieren.

## Fehlerbehebung

Melden Sie Programmfehler und stellen Sie Supportanfragen entweder auf
[Github](https://github.com/cmb69/schedule_xh/issues) oder im
[CMSimple_XH Forum](https://cmsimpleforum.com/).

## Lizenz

Schedule_XH ist freie Software. Sie können es unter den Bedingungen der
GNU General Public License, wie von der Free Software Foundation
veröffentlicht, weitergeben und/oder modifizieren, entweder gemäß
Version 3 der Lizenz oder (nach Ihrer Option) jeder späteren Version.

Die Veröffentlichung von Schedule_XH erfolgt in der Hoffnung, daß es
Ihnen von Nutzen sein wird, aber ohne irgendeine Garantie, sogar ohne
die implizite Garantie der Marktreife oder der Verwendbarkeit für einen
bestimmten Zweck. Details finden Sie in der GNU General Public License.

Sie sollten ein Exemplar der GNU General Public License zusammen mit
Schedule_XH erhalten haben. Falls nicht, siehe <https://www.gnu.org/licenses/>.

Copyright 2012-2022 Christoph M. Becker

## Danksagung

Das Plugin wurde von *Roymcavoy* angeregt.

Das Plugin-Icon wurde von [schollidesign](https://www.deviantart.com/schollidesign) entworfen.
Vielen Dank für die Veröffentlichung unter GPL.

Vielen Dank an die Gemeinde im [CMSimple_XH Forum](https://www.cmsimpleforum.com/)
für Hinweise, Vorschläge und das Testen.
Besonders möchte ich *Ele* für das Beta-Testen und gute Verbesserungsvorschläge danken.

Und zu guter letzt vielen Dank an [Peter Harteg](https://www.harteg.dk/),
den “Vater” von CMSimple, und allen Entwicklern von [CMSimple_XH](https://www.cmsimple-xh.org/de/)
ohne die es dieses phantastische CMS nicht gäbe.
