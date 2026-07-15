###[DEF]###
[name = Remanent Relais 1/0 1.0]
[titel = Remanent Relais 1/0 1.0]
[version = 1.0]

[e#1 TRIGGER = Schalten 1/0]

[a#1 = Status 1/0]

[v#1 REMANENT = 0] Status 1/0
###[/DEF]###

###[HELP]###
Version: 1.0

Remanent Relais 1/0 (19100844)

Zweck:
- Speichert einen einfachen 1/0-Schaltzustand remanent.
- Gibt den zuletzt gespeicherten Zustand nach Neustart/Aktivierung wieder auf A1 aus.
- Nur die Werte 0 und 1 werden uebernommen.

Eingaenge:
- E1: Schalten 1/0. Bei Aktualisierung mit 1 wird der Status 1 gespeichert und ausgegeben. Bei Aktualisierung mit 0 wird der Status 0 gespeichert und ausgegeben.

Ausgaenge:
- A1: Status 1/0. Entspricht dem gespeicherten Zustand.

Ablauf:
- Bei jedem Aufruf gibt der Baustein den remanent gespeicherten Status auf A1 aus.
- Wenn E1 aktualisiert wird und exakt 0 oder 1 enthaelt, wird V1 remanent aktualisiert.
- Andere Werte an E1 werden ignoriert und aendern den gespeicherten Status nicht.

Hinweise:
- Reiner LBS-Baustein, kein EXEC-Code.
- Fuer mehrere Schaltziele A1 mit mehreren Ausgangsboxen verknuepfen.
###[/HELP]###

###[LBS]###
<?php
function _lbs19100844_binary_value($value) {
    $value = trim((string)$value);
    if ($value === '0') return 0;
    if ($value === '1') return 1;
    return null;
}

function LB_LBSID($id) {
    if (!($E = logic_getInputs($id))) return;

    $state = _lbs19100844_binary_value(logic_getVar($id, 1));
    if ($state === null) $state = 0;

    if (isset($E[1]) && intval($E[1]['refresh']) == 1) {
        $input = _lbs19100844_binary_value($E[1]['value']);
        if ($input !== null) {
            $state = $input;
            logic_setVar($id, 1, $state);
        }
    }

    logic_setOutput($id, 1, $state);
}
?>
###[/LBS]###

###[EXEC]###
<?
?>
###[/EXEC]###
