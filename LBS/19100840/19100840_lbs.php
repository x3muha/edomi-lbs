###[DEF]###
[name = KNX DPT 20.105 Mapper 1.0]
[titel = KNX DPT 20.105 HVAC Control Mode Mapper 1.0]
[version = 1.0]

[e#1 TRIGGER = Betriebsart Setzen]
[e#2 = Auto Status 1/0]
[e#3 = Heizen Status 1/0]
[e#4 = Kühlen Status 1/0]
[e#5 = Lüften Status 1/0]
[e#6 = Entfeuchten Status 1/0]

[a#1 = Betriebsart Status]
[a#2 = Auto Setzen]
[a#3 = Heizen Setzen]
[a#4 = Kühlen Setzen]
[a#5 = Lüften Setzen]
[a#6 = Entfeuchten Setzen]
[a#7 = Status Text]
[a#8 = Status gültig 1/0]
###[/DEF]###

###[HELP]###
Version: 1.0

KNX DPT 20.105 Mapper (19100840)

Zweck:
- Wandelt einen DPT-20.105-Betriebsartwert in einzelne 1/0-Setzausgänge.
- Wandelt einzelne 1/0-Status-Eingänge wieder in einen DPT-20.105-Statuswert.

DPT 20.105 Werte:
- 0: Auto
- 1: Heizen
- 3: Kühlen
- 9: Lüften
- 14: Entfeuchten

Eingänge:
- E1: Betriebsart Setzen. DPT-20.105-Wert 0/1/3/9/14.
- E2: Auto Status 1/0.
- E3: Heizen Status 1/0.
- E4: Kühlen Status 1/0.
- E5: Lüften Status 1/0.
- E6: Entfeuchten Status 1/0.

Ausgänge:
- A1: Betriebsart Status als DPT-20.105-Wert.
- A2: Auto Setzen 1/0.
- A3: Heizen Setzen 1/0.
- A4: Kühlen Setzen 1/0.
- A5: Lüften Setzen 1/0.
- A6: Entfeuchten Setzen 1/0.
- A7: Status Text.
- A8: Status gültig 1/0.

Ablauf:
- Wenn E1 aktualisiert wird, setzt der Baustein genau einen Ausgang A2..A6 auf 1 und die anderen auf 0.
- Wenn ein Status-Eingang E2..E6 aktualisiert wird, setzt der Baustein A1 auf den passenden DPT-20.105-Wert.
- Wenn mehrere Status-Eingänge gleichzeitig 1 sind, gewinnt der zuerst gefundene aktive Status in der Reihenfolge Auto, Heizen, Kühlen, Lüften, Entfeuchten.
- Wenn kein Status-Eingang aktiv ist, wird A1 nicht neu geschrieben, A7 wird "Kein Status" und A8 wird 0.
- Unbekannte DPT-Werte an E1 schreiben keine Setzausgänge und setzen A7 auf "Unbekannt: <Wert>".

Hinweise:
- Reiner LBS-Baustein, kein EXEC-Code.
- DPT 20.105 wird hier als HVAC Control Mode für Klima-/Split-Gateways abgebildet.
###[/HELP]###

###[LBS]###
<?php
function _dpt20105_modes(){
    return array(
        2=>array('value'=>0,'name'=>'Auto'),
        3=>array('value'=>1,'name'=>'Heizen'),
        4=>array('value'=>3,'name'=>'Kühlen'),
        5=>array('value'=>9,'name'=>'Lüften'),
        6=>array('value'=>14,'name'=>'Entfeuchten')
    );
}

function _dpt20105_mode_by_value($value){
    foreach(_dpt20105_modes() as $idx=>$mode){
        if(intval($mode['value'])===intval($value)) return array('idx'=>$idx,'mode'=>$mode);
    }
    return null;
}

function _dpt20105_write_set_outputs($id,$selectedInputIdx){
    foreach(_dpt20105_modes() as $inputIdx=>$mode){
        logic_setOutput($id,$inputIdx,($inputIdx===$selectedInputIdx) ? 1 : 0);
    }
}

function _dpt20105_status_from_inputs($E){
    foreach(_dpt20105_modes() as $idx=>$mode){
        if(isset($E[$idx]) && intval($E[$idx]['value'])==1) return $mode;
    }
    return null;
}

function _dpt20105_status_triggered($E){
    foreach(_dpt20105_modes() as $idx=>$mode){
        if(isset($E[$idx]) && intval($E[$idx]['refresh'])==1) return true;
    }
    return false;
}

function LB_LBSID($id) {
    if (!($E=logic_getInputs($id))) return;

    if(isset($E[1]) && intval($E[1]['refresh'])==1){
        $selected=_dpt20105_mode_by_value($E[1]['value']);
        if($selected!==null){
            _dpt20105_write_set_outputs($id,$selected['idx']);
            logic_setOutput($id,7,$selected['mode']['name']);
            logic_setOutput($id,8,1);
        }else{
            logic_setOutput($id,7,'Unbekannt: '.$E[1]['value']);
            logic_setOutput($id,8,0);
        }
    }

    if(_dpt20105_status_triggered($E)){
        $status=_dpt20105_status_from_inputs($E);
        if($status!==null){
            logic_setOutput($id,1,$status['value']);
            logic_setOutput($id,7,$status['name']);
            logic_setOutput($id,8,1);
        }else{
            logic_setOutput($id,7,'Kein Status');
            logic_setOutput($id,8,0);
        }
    }
}
?>
###[/LBS]###

###[EXEC]###
<?
?>
###[/EXEC]###
