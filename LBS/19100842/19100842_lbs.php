###[DEF]###
[name = Wh Leistung Rechner 1.0]
[version = 1.0]

[e#1 = Zaehlerstand Wh]
[e#2 = Aktiv 1/0 #init=1]
[e#3 = Mindestzeit s #init=1]
[e#4 = Max Leistung W (0=aus) #init=0]
[e#5 = Nachkommastellen #init=0]
[e#6 = Reset/Baseline 1]

[a#1 = Leistung W]
[a#2 = Differenz Wh]
[a#3 = Zeitdifferenz s]
[a#4 = Zaehlerstand aktuell Wh]
[a#5 = Zaehlerstand vorher Wh]
[a#6 = OK 1/0]
[a#7 = Status Text]
[a#8 = Zeitstempel Unix]

[v#1 REMANENT = 0] Baseline initialisiert
[v#2 REMANENT = 0] Letzter Zaehlerstand Wh
[v#3 REMANENT = 0] Letzter Zeitstempel Unix
###[/DEF]###

###[HELP]###
Version: 1.0

Wh Leistung Rechner 1.0

Zweck:
- Berechnet aus einem fortlaufenden Wh-Zaehler die aktuelle Leistung in W.
- Grundlage ist: Leistung W = Differenz Wh * 3600 / Zeitdifferenz Sekunden.
- Der letzte Zaehlerstand und Zeitstempel werden remanent gespeichert.

Betrieb:
- E1 = fortlaufender Zaehlerstand in Wh.
- E2 = Aktiv 1/0, Standard 1.
- E3 = Mindestzeit in Sekunden zwischen zwei gueltigen Berechnungen, Standard 1.
- E4 = optionale Plausibilitaetsgrenze in W, 0 = aus.
- E5 = Nachkommastellen fuer A1, A2 und A3, Standard 0.
- E6 = Reset/Baseline. Bei 1 wird der aktuelle Zaehlerstand als neue Basis gespeichert.

Ausgaenge:
- A1 = berechnete Leistung in W.
- A2 = verwendete Zaehlerdifferenz in Wh.
- A3 = verwendete Zeitdifferenz in Sekunden.
- A4 = aktueller Zaehlerstand in Wh.
- A5 = vorheriger Zaehlerstand in Wh.
- A6 = OK 1/0.
- A7 = Status Text.
- A8 = Unix-Zeitstempel der Berechnung.

Ablauf:
- Beim ersten gueltigen Zaehlerwert oder bei Reset wird nur die Baseline gesetzt.
- Bei jedem neuen Zaehlerwert wird die Differenz zum letzten gueltigen Wert berechnet.
- Ist die Zeitdifferenz kleiner als E3, wird noch keine neue Leistung berechnet.
- Wenn der Zaehlerstand kleiner als der vorherige Wert ist, wird ein Ruecksprung erkannt und eine neue Baseline gesetzt.
- Wenn E4 > 0 ist und die berechnete Leistung groesser als E4 ist, wird der Wert verworfen und die Baseline bleibt erhalten.
- Bei verworfenem Wert bleibt A1 auf dem letzten gueltigen Leistungswert.
- Bei gueltiger Berechnung werden Zaehlerstand und Zeitstempel als neue Baseline gespeichert.

Hinweise:
- Reiner LBS-Baustein, kein EXEC-Code.
- Der Baustein erwartet einen monoton steigenden Energiezaehler in Wh.
- Fuer kWh-Zaehler den Wert vor E1 mit 1000 multiplizieren.
###[/HELP]###

###[LBS]###
<?php
function _lbs19100842_float($value) {
    $value = str_replace(',', '.', trim((string)$value));
    if ($value === '' || !is_numeric($value)) return null;
    return floatval($value);
}

function _lbs19100842_round($value, $digits) {
    $digits = max(0, intval($digits));
    return round(floatval($value), $digits);
}

function _lbs19100842_set_baseline($id, $wh, $ts, $status, $ok) {
    logic_setVar($id, 1, 1);
    logic_setVar($id, 2, $wh);
    logic_setVar($id, 3, $ts);
    logic_setOutput($id, 1, 0);
    logic_setOutput($id, 2, 0);
    logic_setOutput($id, 3, 0);
    logic_setOutput($id, 4, $wh);
    logic_setOutput($id, 5, $wh);
    logic_setOutput($id, 6, $ok ? 1 : 0);
    logic_setOutput($id, 7, $status);
    logic_setOutput($id, 8, $ts);
}

function LB_LBSID($id) {
    if (!($E = logic_getInputs($id))) return;

    $enabled = intval($E[2]['value']) == 1;
    if (!$enabled) {
        logic_setOutput($id, 6, 0);
        logic_setOutput($id, 7, 'Inaktiv');
        return;
    }

    $currentWh = _lbs19100842_float($E[1]['value']);
    if ($currentWh === null) {
        logic_setOutput($id, 6, 0);
        logic_setOutput($id, 7, 'Zaehlerstand ungueltig');
        return;
    }

    $now = time();
    $reset = isset($E[6]) && intval($E[6]['refresh']) == 1 && intval($E[6]['value']) == 1;
    if (intval(logic_getVar($id, 1)) != 1 || $reset) {
        _lbs19100842_set_baseline($id, $currentWh, $now, 'Baseline gesetzt', 1);
        return;
    }

    if (isset($E[1]) && intval($E[1]['refresh']) != 1) return;

    $lastWh = floatval(logic_getVar($id, 2));
    $lastTs = intval(logic_getVar($id, 3));
    if ($lastTs <= 0) {
        _lbs19100842_set_baseline($id, $currentWh, $now, 'Baseline gesetzt', 1);
        return;
    }

    $diffWh = $currentWh - $lastWh;
    $diffSec = $now - $lastTs;
    $digits = max(0, intval($E[5]['value']));

    logic_setOutput($id, 4, $currentWh);
    logic_setOutput($id, 5, $lastWh);
    logic_setOutput($id, 8, $now);

    if ($diffWh < 0) {
        _lbs19100842_set_baseline($id, $currentWh, $now, 'Ruecksprung erkannt, Baseline neu gesetzt', 0);
        return;
    }

    $minSec = max(1, intval($E[3]['value']));
    if ($diffSec < $minSec) {
        logic_setOutput($id, 2, _lbs19100842_round($diffWh, $digits));
        logic_setOutput($id, 3, $diffSec);
        logic_setOutput($id, 6, 0);
        logic_setOutput($id, 7, 'Warte Mindestzeit');
        return;
    }

    $powerW = ($diffSec > 0) ? ($diffWh * 3600.0 / $diffSec) : 0;
    $maxPower = _lbs19100842_float($E[4]['value']);
    if ($maxPower !== null && $maxPower > 0 && $powerW > $maxPower) {
        logic_setOutput($id, 2, _lbs19100842_round($diffWh, $digits));
        logic_setOutput($id, 3, _lbs19100842_round($diffSec, $digits));
        logic_setOutput($id, 6, 0);
        logic_setOutput($id, 7, 'Leistung ueber Max, verworfen');
        return;
    }

    logic_setOutput($id, 1, _lbs19100842_round($powerW, $digits));
    logic_setOutput($id, 2, _lbs19100842_round($diffWh, $digits));
    logic_setOutput($id, 3, _lbs19100842_round($diffSec, $digits));
    logic_setOutput($id, 6, 1);
    logic_setOutput($id, 7, 'OK');
    logic_setVar($id, 2, $currentWh);
    logic_setVar($id, 3, $now);
}
?>
###[/LBS]###

###[EXEC]###
<?
?>
###[/EXEC]###
