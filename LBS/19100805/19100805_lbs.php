###[DEF]###
[name = cFos Wallbox Decoder 1.0]
[version = 1.0]

[e#1 = Wallbox In (JSON)]
[e#2 = Wallbox-ID (z.B. E1)]

[a#1 = OK]
[a#2 = Fehlertext]
[a#3 = Geräte-ID]
[a#4 = Name]
[a#5 = Gewählte Wallbox (JSON)]
[a#6 = Status (Rohwert)]
[a#7 = Fahrzeug eingesteckt]
[a#8 = Lädt]
[a#9 = Fehler vorhanden]
[a#10 = Offline]
[a#11 = Gerät aktiviert]
[a#12 = Laden freigegeben]
[a#13 = Pausiert]
[a#14 = Aktuelle Ladeleistung (W)]
[a#15 = Gesamtenergie (Wh)]
[a#16 = Minimalstrom (mA)]
[a#17 = Maximalstrom (mA)]
[a#18 = Phasen (Bitmaske)]
[a#19 = Phase L1 aktiv]
[a#20 = Phase L2 aktiv]
[a#21 = Phase L3 aktiv]
[a#22 = Einphasig]
[a#23 = Dreiphasig]
[a#24 = Kommunikationsfehler]
[a#25 = Kommunikationsfehler Anzahl]
[a#26 = Letzter Fehlertext]
[a#27 = Modell]
[a#28 = Priorität]
[a#29 = Firmware-Version]
[a#30 = Seriennummer]

[v#100 = ] letzte Ausgaenge JSON
###[/DEF]###

###[HELP]###
Version: 1.0

cFos Wallbox Decoder (19100805)

Zweck:
- Dekodiert eine einzelne Wallbox aus dem Reader-JSON (A30 von 19100804).

Eingänge:
- E1 Wallbox-JSON (Liste)
- E2 gewünschte dev_id (z. B. E1)

Kernlogik:
- Sucht exakt die gewünschte Wallbox-ID.
- Dekodiert state, charging, enabled, paused, power, phases, com-error, model etc.
- Zerlegt model-String zusätzlich in Firmware (A29) und Seriennummer (A30).

Statusinterpretation:
- A7 Fahrzeug eingesteckt bei state 2..5
- A8 lädt bei state 3/4
- A9 Fehler bei state 5 oder Kommunikationsfehler
- A10 offline bei state 6

###[/HELP]###

###[LBS]###
<?php
function _cfos05_set_changed($id,$out,$val){
    static $cache=array();
    if(!isset($cache[$id])){
        $raw=logic_getVar($id,100);
        $tmp=json_decode((string)$raw,true);
        $cache[$id]=is_array($tmp)?$tmp:array();
    }
    $k=strval($out);
    if(!array_key_exists($k,$cache[$id]) || strval($cache[$id][$k])!==strval($val)){
        logic_setOutput($id,$out,$val);
        $cache[$id][$k]=$val;
        logic_setVar($id,100,json_encode($cache[$id]));
    }
}

function LB_LBSID($id) {
    if (!($E=logic_getInputs($id))) return;
    if ($E[1]['refresh']!=1 && $E[2]['refresh']!=1) return;

    $set = function($idx, $val) use ($id) { _cfos05_set_changed($id, $idx, $val); };
    $bool = function($v){ return ($v===true || $v===1 || $v==='1' || $v==='true') ? 1 : 0; };
    $clear = function($msg) use ($set) {
        $set(1,0); $set(2,$msg);
        $set(3,''); $set(4,''); $set(5,'{}'); $set(6,0);
        for($i=7;$i<=24;$i++) $set($i,0);
        $set(25,0); $set(26,''); $set(27,''); $set(28,0); $set(29,''); $set(30,'');
    };

    $json = trim((string)$E[1]['value']);
    $devId = trim((string)$E[2]['value']);

    if ($json === '') { $clear('Wallbox In leer'); return; }
    if ($devId === '') { $clear('Wallbox-ID leer'); return; }

    $arr = json_decode($json, true);
    if (!is_array($arr)) { $clear('Wallbox In JSON ungueltig'); return; }

    $chosen = null;
    foreach ($arr as $d) {
        if (is_array($d) && isset($d['dev_id']) && strval($d['dev_id']) === $devId) { $chosen = $d; break; }
    }

    if (!is_array($chosen)) { $clear('Wallbox-ID nicht gefunden: '.$devId); return; }

    $state = intval(isset($chosen['state']) ? $chosen['state'] : 0);
    $comErr = $bool(isset($chosen['com_err']) ? $chosen['com_err'] : false);
    $ph = intval(isset($chosen['phases']) ? $chosen['phases'] : 0);
    $l1 = ($ph & 1) ? 1 : 0; $l2 = ($ph & 2) ? 1 : 0; $l3 = ($ph & 4) ? 1 : 0;
    $cnt = $l1 + $l2 + $l3;

    $set(3, strval(isset($chosen['dev_id']) ? $chosen['dev_id'] : ''));
    $set(4, strval(isset($chosen['name']) ? $chosen['name'] : ''));
    $set(5, json_encode($chosen, JSON_UNESCAPED_UNICODE));
    $set(6, $state);
    $set(7, ($state>=2 && $state<=5) ? 1 : 0);
    $set(8, ($state==3 || $state==4) ? 1 : 0);
    $set(9, ($state==5 || $comErr) ? 1 : 0);
    $set(10, ($state==6) ? 1 : 0);
    $set(11, $bool(isset($chosen['device_enabled']) ? $chosen['device_enabled'] : false));
    $set(12, $bool(isset($chosen['charging_enabled']) ? $chosen['charging_enabled'] : false));
    $set(13, $bool(isset($chosen['paused']) ? $chosen['paused'] : false));
    $set(14, intval(isset($chosen['cur_charging_power']) ? $chosen['cur_charging_power'] : 0));
    $set(15, intval(isset($chosen['total_energy']) ? $chosen['total_energy'] : 0));
    $set(16, intval(isset($chosen['min_charging_cur']) ? $chosen['min_charging_cur'] : 0));
    $set(17, intval(isset($chosen['max_charging_cur']) ? $chosen['max_charging_cur'] : 0));
    $set(18, $ph);
    $set(19, $l1); $set(20, $l2); $set(21, $l3);
    $set(22, ($cnt==1)?1:0);
    $set(23, ($l1&&$l2&&$l3)?1:0);
    $set(24, $comErr);
    $set(25, intval(isset($chosen['com_errors']) ? $chosen['com_errors'] : 0));
    $set(26, strval(isset($chosen['last_error']) ? $chosen['last_error'] : ''));
    $set(27, strval(isset($chosen['model']) ? $chosen['model'] : ''));
    $set(28, intval(isset($chosen['prio']) ? $chosen['prio'] : 0));

    $model = strval(isset($chosen['model']) ? $chosen['model'] : '');
    $parts = array_map('trim', explode(',', $model));
    $fw = (count($parts) >= 4) ? $parts[3] : '';
    $sn = (count($parts) >= 5) ? $parts[4] : '';
    $set(29, $fw);
    $set(30, $sn);

    $set(1,1); $set(2,'');
}
?>
###[/LBS]###

###[EXEC]###
<?php
?>
###[/EXEC]###
