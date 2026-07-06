###[DEF]###
[name = SolarAssistant Decoder]
[version = 2.1]

[e#1 = IP/URL (z.B. http://192.168.1.50)]
[e#2 = API-Pfad #init=/api/v1/metrics]
[e#3 = User]
[e#4 = Passwort]
[e#5 = Aktiv 1/0]
[e#6 = Intervall Sekunden]
[e#28 = Set max_grid_charge_current (A)]
[e#29 = Set max_charge_current (A)]
[e#30 = Set capacity_point_6 (%)]
[e#31 = Set force_off_grid]

[a#1 = OK]
[a#2 = Fehlertext]
[a#3 = Anzahl Datensaetze]
[a#4 = JSON komplett (normalisiert)]
[a#5 = JSON Status-Gruppe]
[a#6 = JSON System]
[a#10 = total/pv_power | PV Leistung (W)]
[a#11 = total/load_power | Lastleistung gesamt (W)]
[a#12 = total/battery_power | Batterieleistung (W)]
[a#13 = total/grid_power | Netzleistung (W)]
[a#14 = total/battery_state_of_charge | Batterie SoC (%)]
[a#15 = total/battery_voltage | Batteriespannung (V)]
[a#16 = total/battery_current | Batteriestrom (A)]
[a#17 = total/load_percentage | Lastanteil (%)]
[a#18 = inverter_1/load_power | Lastleistung Inverter (W)]
[a#19 = inverter_1/load_power_essential | Lastleistung Essential (W)]
[a#20 = inverter_1/load_power_non-essential | Lastleistung Non-Essential (W)]
[a#21 = inverter_1/ac_couple_pv_power | AC-Coupled PV Leistung (W)]
[a#22 = inverter_1/auxiliary_pv_power | Auxiliary PV Leistung (W)]
[a#23 = inverter_1/max_solar_power | Max. Solarleistung (W)]
[a#24 = total/ac_output_voltage | AC Ausgangsspannung (V)]
[a#25 = total/capacity | Capacity (Ah)]
[a#26 = total/ac_output_frequency | AC output frequency (Hz)]
[a#28 = inverter_1/max_grid_charge_current | Max grid charge current (A)]
[a#29 = inverter_1/max_charge_current | Max charge current (A)]
[a#30 = inverter_1/capacity_point_6 | Capacity point 6 (%)]
[a#31 = inverter_1/force_off_grid | Force off grid]
[a#33 = total/battery_energy_in | Battery energy in (kWh)]
[a#34 = total/battery_energy_out | Battery energy out (kWh)]
[a#35 = total/battery_temperature | Battery temperature (°C)]
[a#36 = total/grid_energy_in | Grid energy in (kWh)]
[a#37 = total/grid_energy_out | Grid energy out (kWh)]
[a#38 = total/grid_frequency | Grid frequency (Hz)]
[a#39 = total/grid_voltage | Grid voltage (V)]
[a#40 = total/inverter_mode | Inverter mode]
[a#41 = total/load_energy | Load energy (kWh)]
[a#42 = total/pv_energy | PV energy (kWh)]
[a#43 = system/site_id | Site ID]
[a#44 = system/software_version | Software version]
[a#45 = system/cpu_temperature | CPU temperature (°C)]
[a#46 = system/free_storage | Free storage (MB)]

[v#1 = 0]
[v#2 = -1]
[v#28 = ] Letzter Schreibwert E28
[v#29 = ] Letzter Schreibwert E29
[v#30 = ] Letzter Schreibwert E30
[v#31 = ] Letzter Schreibwert E31
[v#91 = 0] Pending EXEC
[v#92 = 0] Schreibwert-Baseline initialisiert
###[/DEF]###

###[HELP]###
Version: 2.1

SolarAssistant Decoder (HTTP Polling)

Zweck:
- Holt JSON zyklisch von SolarAssistant REST API und gibt Kernwerte auf feste Ausgaenge aus.
- Version 1.1 ergaenzt alle weiteren aktuellen total/* Werte und /api/v1/system.
- Version 2.0 entfernt die sechs Battery-Cell-Detailwerte A27..A32,
  laesst A27 und A32 frei und nutzt A28..A31 fuer Status plus direkte
  Schreibeingaenge mit gleicher Nummer.
- Bei ungueltiger/leerer Antwort bleiben letzte gueltige Nutzwerte stehen.

Betrieb:
- E1 = Basis-URL/IP (z. B. http://192.168.1.50)
- E2 = API-Pfad (Standard /api/v1/metrics)
- E3/E4 = User/Passwort
- E5 = Aktiv (1 startet zyklische Abfrage)
- E6 = Intervall in Sekunden
- E28..E31 schreiben bei Eingang-Refresh den Wert per REST auf das
  gleich nummerierte SolarAssistant-Topic.
- Version 2.1 schreibt E28..E31 ueber den offiziellen REST-Endpunkt
  POST /api/v1/metrics, erkennt zusaetzlich Wertaenderungen ohne Refresh-Flag
  und zeigt Schreib-Erfolg oder SolarAssistant-Fehler in A2 an.
- HTTP/cURL laeuft im EXEC-Teil, damit die Logik nicht blockiert.

Ausgaenge:
- A10..A24: bisherige Kernwerte.
- A25..A26 und A33..A42: weitere total/* Werte aus der aktuellen API.
- A28..A31: ausgewaehlte Inverter-Statuswerte mit gleich nummeriertem
  Schreibeingang.
- A43..A46: system/* Werte aus /api/v1/system.
- A27 und A32 bleiben bewusst frei.

Schreiben:
- MQTT-Topic `solar_assistant/inverter_1/capacity_point_6/set` entspricht
  REST-Set auf `inverter_1/capacity_point_6`.
- Ein Refresh auf E28..E31 schreibt sofort den jeweiligen Eingangswert.
- Falls EDOMI beim manuellen Eintragen kein Refresh-Flag setzt, schreibt der
  Baustein den geaenderten nicht-leeren Wert beim naechsten Lauf.

Fehlerbild:
- A2 liefert Text bei URL/Auth/JSON-Fehlern.

###[/HELP]###

###[LBS]###
<?php
function LB_LBSID($id) {
    if (!($E=logic_getInputs($id))) return;

    $enabled = (intval($E[5]['value'])==1);
    $interval = max(1, intval($E[6]['value']));
    $writeRefresh = false;
    $writeBaselineReady = (intval(logic_getVar($id,92))==1);
    foreach (array(28,29,30,31) as $writeIdx) {
        $writeVal = isset($E[$writeIdx]) ? trim((string)$E[$writeIdx]['value']) : '';
        if ($E[$writeIdx]['refresh']==1 || ($writeBaselineReady && $writeVal!=='' && $writeVal!==(string)logic_getVar($id,$writeIdx))) {
            $writeRefresh = true;
            break;
        }
    }
    $needRun = false;

    if ($E[5]['refresh']==1) {
        if ($enabled) { logic_setVar($id,1,1); $needRun = true; }
        else if ($writeRefresh) { logic_setVar($id,1,0); $needRun = true; }
        else { logic_setVar($id,1,0); logic_setVar($id,91,0); logic_setState($id,0); return; }
    }
    if ($E[1]['refresh']==1 || $E[2]['refresh']==1 || $E[3]['refresh']==1 || $E[4]['refresh']==1 || $E[6]['refresh']==1) {
        if ($enabled) $needRun = true;
    }
    if (intval(logic_getVar($id,91))==1 && $enabled) $needRun = true;
    if (intval(logic_getState($id))==1 && intval(logic_getVar($id,1))==1 && $enabled) $needRun = true;
    if ($writeRefresh) $needRun = true;
    if (!$needRun) return;

    logic_setInputsQueued($id,$E);
    if (logic_getStateExec($id)==0) {
        logic_setVar($id,91,0);
        logic_callExec(LBSID,$id,false);
    } else {
        logic_setVar($id,91,1);
        logic_setState($id,1,1000);
        return;
    }

    if (intval(logic_getVar($id,1))==1 && intval($E[5]['value'])==1) logic_setState($id,1,$interval*1000);
    else logic_setState($id,0);
}
?>
###[/LBS]###

###[EXEC]###
<?php
require(dirname(__FILE__)."/../../../../main/include/php/incl_lbsexec.php");
set_time_limit(30);
sql_connect();

function sa806_http_set($base,$user,$pass,$topic,$value,&$err) {
    if (!function_exists('curl_init')) { $err='curl fehlt'; return false; }
    $url=rtrim($base,'/').'/api/v1/metrics';
    $payload=json_encode(array('topic'=>$topic,'value'=>(string)$value));
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_POST,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$payload);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
    curl_setopt($ch,CURLOPT_TIMEOUT,15);
    curl_setopt($ch,CURLOPT_USERPWD,$user.':'.$pass);
    curl_setopt($ch,CURLOPT_HTTPHEADER,array('Accept: application/json','Content-Type: application/json'));
    if (stripos($url,'https://')===0) { curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false); curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false); }
    $resp=curl_exec($ch);
    $code=intval(curl_getinfo($ch,CURLINFO_HTTP_CODE));
    if ($resp===false) { $err='curl: '.curl_error($ch); curl_close($ch); return false; }
    curl_close($ch);

    $data=json_decode((string)$resp,true);
    if ($code<200 || $code>=300) {
        if (is_array($data) && isset($data['message'])) $err='HTTP '.$code.': '.strval($data['message']);
        else if (trim((string)$resp)!=='') $err='HTTP '.$code.': '.trim((string)$resp);
        else $err='HTTP '.$code;
        return false;
    }
    if (is_array($data) && isset($data['result']) && $data['result']==='ok') return true;
    if (is_array($data) && isset($data['error'])) { $err=strval($data['error']); return false; }
    $err='Antwort ohne result=ok';
    return false;
}

if (!($E=logic_getInputsQueued($id))) {
    $E=logic_getInputs($id);
}
if (!$E) return;

$set=function($idx,$val) use ($id){ logic_setOutput($id,$idx,$val); };
$base=rtrim(trim((string)$E[1]['value']),'/');
$path=trim((string)$E[2]['value']); if($path==='') $path='/api/v1/metrics'; if($path[0]!='/') $path='/'.$path;
$user=trim((string)$E[3]['value']);
$pass=(string)$E[4]['value'];
$enabled=(intval($E[5]['value'])==1);
$writeMap=array(
        28 => 'inverter_1/max_grid_charge_current',
        29 => 'inverter_1/max_charge_current',
        30 => 'inverter_1/capacity_point_6',
        31 => 'inverter_1/force_off_grid',
);
$writeRequests=array();
$writeBaselineReady = (intval(logic_getVar($id,92))==1);
foreach($writeMap as $inIdx=>$topic) {
    if(!isset($E[$inIdx])) continue;
    $writeValue=trim((string)$E[$inIdx]['value']);
    if($writeValue==='') continue;
    if(intval($E[$inIdx]['refresh'])===1 || ($writeBaselineReady && $writeValue!==(string)logic_getVar($id,$inIdx))) {
        $writeRequests[$topic]=array('input'=>$inIdx,'value'=>$writeValue);
    }
}

$map=array(
        10 => 'total/pv_power',
        11 => 'total/load_power',
        12 => 'total/battery_power',
        13 => 'total/grid_power',
        14 => 'total/battery_state_of_charge',
        15 => 'total/battery_voltage',
        16 => 'total/battery_current',
        17 => 'total/load_percentage',
        18 => 'inverter_1/load_power',
        19 => 'inverter_1/load_power_essential',
        20 => 'inverter_1/load_power_non-essential',
        21 => 'inverter_1/ac_couple_pv_power',
        22 => 'inverter_1/auxiliary_pv_power',
        23 => 'inverter_1/max_solar_power',
        24 => 'total/ac_output_voltage',
        25 => 'total/capacity',
        26 => 'total/ac_output_frequency',
        28 => 'inverter_1/max_grid_charge_current',
        29 => 'inverter_1/max_charge_current',
        30 => 'inverter_1/capacity_point_6',
        31 => 'inverter_1/force_off_grid',
        33 => 'total/battery_energy_in',
        34 => 'total/battery_energy_out',
        35 => 'total/battery_temperature',
        36 => 'total/grid_energy_in',
        37 => 'total/grid_energy_out',
        38 => 'total/grid_frequency',
        39 => 'total/grid_voltage',
        40 => 'total/inverter_mode',
        41 => 'total/load_energy',
        42 => 'total/pv_energy',
);
$systemMap=array(
        43 => 'system/site_id',
        44 => 'system/software_version',
        45 => 'system/cpu_temperature',
        46 => 'system/free_storage',
);

if (!$enabled && count($writeRequests)===0) return;
if ($base==='') { $set(2,'IP/URL leer'); return; }
if ($user==='') { $set(2,'User fehlt'); return; }
if (!function_exists('curl_init')) { $set(2,'curl fehlt'); return; }

$writeErrors=array();
$writeOk=array();
foreach($writeRequests as $topic=>$req) {
    $err='';
    if(sa806_http_set($base,$user,$pass,$topic,$req['value'],$err)) {
        logic_setVar($id,$req['input'],$req['value']);
        $writeOk[]=$topic.'='.$req['value'];
    } else {
        $writeErrors[]=$topic.': '.$err;
    }
}
foreach($writeMap as $inIdx=>$topic) {
    if(isset($E[$inIdx]) && trim((string)$E[$inIdx]['value'])!=='' && !isset($writeRequests[$topic])) {
        logic_setVar($id,$inIdx,trim((string)$E[$inIdx]['value']));
    }
}
logic_setVar($id,92,1);

$url=$base.$path;
$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
curl_setopt($ch,CURLOPT_TIMEOUT,15);
curl_setopt($ch,CURLOPT_USERPWD,$user.':'.$pass);
curl_setopt($ch,CURLOPT_HTTPHEADER,array('Accept: application/json'));
if (stripos($url,'https://')===0) { curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false); curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false); }
$resp=curl_exec($ch);
if ($resp===false) { $set(2,'curl: '.curl_error($ch)); curl_close($ch); return; }
curl_close($ch);

$arr=json_decode($resp,true);
if(!is_array($arr) || count($arr)===0) { $set(2,'JSON ungueltig/leer'); return; }

$status=array();
foreach ($arr as $row) {
    if (!is_array($row)) continue;
    $group = isset($row['group']) ? strval($row['group']) : '';
    if (strtolower($group)==='status') $status[]=$row;
}
$set(3,count($arr));
$set(4,json_encode($arr,JSON_UNESCAPED_UNICODE));
$set(5,json_encode($status,JSON_UNESCAPED_UNICODE));

$byTopic=array();
foreach($arr as $row) {
    if(!is_array($row)) continue;
    $topic=isset($row['topic'])?strval($row['topic']):'';
    if($topic==='' || array_key_exists($topic,$byTopic)) continue;
    $byTopic[$topic]=$row;
}
foreach($map as $outIdx=>$topic) {
    if(isset($byTopic[$topic]) && is_array($byTopic[$topic]) && array_key_exists('value',$byTopic[$topic])) $set($outIdx,$byTopic[$topic]['value']);
    else $set($outIdx,0);
}

$systemUrl=$base.'/api/v1/system';
$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,$systemUrl);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
curl_setopt($ch,CURLOPT_TIMEOUT,15);
curl_setopt($ch,CURLOPT_USERPWD,$user.':'.$pass);
curl_setopt($ch,CURLOPT_HTTPHEADER,array('Accept: application/json'));
if (stripos($systemUrl,'https://')===0) { curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false); curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false); }
$systemResp=curl_exec($ch);
curl_close($ch);
$systemArr=json_decode((string)$systemResp,true);
if(is_array($systemArr)) {
    $set(6,json_encode($systemArr,JSON_UNESCAPED_UNICODE));
    $systemByTopic=array();
    foreach($systemArr as $row) {
        if(!is_array($row)) continue;
        $topic=isset($row['topic'])?strval($row['topic']):'';
        if($topic==='' || array_key_exists($topic,$systemByTopic)) continue;
        $systemByTopic[$topic]=$row;
    }
    foreach($systemMap as $outIdx=>$topic) {
        if(isset($systemByTopic[$topic]) && is_array($systemByTopic[$topic]) && array_key_exists('value',$systemByTopic[$topic])) $set($outIdx,$systemByTopic[$topic]['value']);
    }
}

if(count($writeErrors)>0) {
    $set(1,0);
    $set(2,implode('; ',$writeErrors));
} else if(count($writeOk)>0) {
    $set(1,1);
    $set(2,'Schreiben OK: '.implode(', ',$writeOk));
} else {
    $set(1,1);
    $set(2,'');
}
?>
###[/EXEC]###
