###[DEF]###
[name = cFos HTTP Reader 4fach 1.0]
[version = 1.0]

[e#1 = Host/IP]
[e#2 = Port #init=80]
[e#3 = HTTPS 0/1 #init=0]
[e#4 = User]
[e#5 = Passwort]
[e#6 = Aktiv 1/0 #init=1]
[e#7 = Intervall Sekunden #init=10]
[e#8 = ID1 (z.B. E1/M4)]
[e#9 = ID2]
[e#10 = ID3]
[e#11 = ID4]
[e#12 = Debug RAW 0/1 #init=0]
[e#13 = SSL pruefen 1/0 #init=0]

[a#1 = OK]
[a#2 = HTTP-Code]
[a#3 = Fehlertext]
[a#4 = Timestamp]
[a#5 = RAW JSON]
[a#6 = Anzahl Devices]
[a#7 = Gefundene IDs]
[a#8 = Status Text]

[a#10 = Gefunden 1]
[a#11 = ID 1]
[a#12 = Name 1]
[a#13 = Modell 1]
[a#14 = Leistung W 1]
[a#15 = Energie Wh 1]
[a#16 = Strom mA 1]
[a#17 = Status 1]
[a#18 = Aktiv/Freigegeben 1]
[a#19 = Device JSON 1]

[a#20 = Gefunden 2]
[a#21 = ID 2]
[a#22 = Name 2]
[a#23 = Modell 2]
[a#24 = Leistung W 2]
[a#25 = Energie Wh 2]
[a#26 = Strom mA 2]
[a#27 = Status 2]
[a#28 = Aktiv/Freigegeben 2]
[a#29 = Device JSON 2]

[a#30 = Gefunden 3]
[a#31 = ID 3]
[a#32 = Name 3]
[a#33 = Modell 3]
[a#34 = Leistung W 3]
[a#35 = Energie Wh 3]
[a#36 = Strom mA 3]
[a#37 = Status 3]
[a#38 = Aktiv/Freigegeben 3]
[a#39 = Device JSON 3]

[a#40 = Gefunden 4]
[a#41 = ID 4]
[a#42 = Name 4]
[a#43 = Modell 4]
[a#44 = Leistung W 4]
[a#45 = Energie Wh 4]
[a#46 = Strom mA 4]
[a#47 = Status 4]
[a#48 = Aktiv/Freigegeben 4]
[a#49 = Device JSON 4]

[v#1 = 0]
[v#90 = 0] Retry nach Fehler
[v#91 = 0] Pending
[v#100 = ] letzte Ausgaenge JSON
###[/DEF]###

###[HELP]###
Version: 1.0

cFos HTTP Reader 4fach (19100830)

Zweck:
- Zyklischer Reader fuer cFos /cnf?cmd=get_dev_info.
- Liest einmal pro Zyklus alle cFos-Devices und dekodiert bis zu 4 frei waehlbare IDs.
- Geeignet fuer Wallboxen (E*) und Zaehler/HTTPMeter (M*), solange die Werte im cFos-Device enthalten sind.

Eingaenge:
- E1..E5 Host/Port/HTTPS/Auth.
- E6=1 startet den Zyklus, E6=0 stoppt.
- E7 Intervall in Sekunden.
- E8..E11 Device-IDs, z. B. E1, E2, M4.
- E12=1 schreibt komplettes RAW JSON auf A5, sonst bleibt A5 leer.
- E13=1 aktiviert SSL-Zertifikatspruefung bei HTTPS; Standard 0 fuer lokale/self-signed cFos-Installationen.

Ausgaenge je Device:
- Gefunden, ID, Name, Modell.
- Leistung W: erster vorhandener Wert aus power_w, cur_power, cur_charging_power, power.
- Energie Wh: erster vorhandener Wert aus total_energy, energy_wh, import_wh, export_wh.
- Strom mA: erster vorhandener Wert aus current, cur, charging_cur, max_charging_cur.
- Status: state oder status.
- Aktiv/Freigegeben: device_enabled oder charging_enabled.
- Device JSON: kompletter gefundener Device-Datensatz.

Hinweise:
- HTTP/curl laeuft im EXEC-Teil, damit ein nicht erreichbarer cFos die Logik nicht blockiert.
- A1=1 bedeutet: cFos erreichbar, JSON gueltig. Einzelne nicht gefundene IDs setzen nur den jeweiligen Gefunden-Ausgang auf 0.
- Ausgaenge werden nur bei Wertwechsel geschrieben. Bei Aktiv=0 bleiben die letzten Ausgangswerte stehen.
###[/HELP]###

###[LBS]###
<?php
function LB_LBSID($id) {
    if (!($E=logic_getInputs($id))) return;

    $enabled = (intval($E[6]['value'])==1);
    $interval = max(1, intval($E[7]['value']));
    $needRun = false;

    if ($E[6]['refresh']==1) {
        if ($enabled) { logic_setVar($id,1,1); $needRun = true; }
        else { logic_setVar($id,1,0); logic_setVar($id,91,0); logic_setState($id,0); return; }
    }

    if ($E[1]['refresh']==1 || $E[2]['refresh']==1 || $E[3]['refresh']==1 ||
        $E[4]['refresh']==1 || $E[5]['refresh']==1 || $E[7]['refresh']==1 ||
        $E[8]['refresh']==1 || $E[9]['refresh']==1 || $E[10]['refresh']==1 ||
        $E[11]['refresh']==1 || $E[12]['refresh']==1 || $E[13]['refresh']==1) {
        if ($enabled) $needRun = true;
    }

    if (intval(logic_getVar($id,91))==1 && $enabled) $needRun = true;
    if (intval(logic_getState($id))==1 && intval(logic_getVar($id,1))==1 && $enabled) $needRun = true;
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

    if (intval(logic_getVar($id,1))==1 && intval($E[6]['value'])==1) logic_setState($id,1,$interval*1000);
    else logic_setState($id,0);
}
?>
###[/LBS]###

###[EXEC]###
<?php
require(dirname(__FILE__)."/../../../../main/include/php/incl_lbsexec.php");
set_time_limit(10);
sql_connect();

function _cfos4r_bool($v){
    return ($v===true || $v===1 || $v==='1' || $v==='true') ? 1 : 0;
}

function _cfos4r_get($a,$k,$d=null){
    return (is_array($a) && array_key_exists($k,$a)) ? $a[$k] : $d;
}

function _cfos4r_first($a,$keys,$d=0){
    if (!is_array($a)) return $d;
    foreach ($keys as $k) {
        if (array_key_exists($k,$a) && $a[$k] !== '' && $a[$k] !== null) return $a[$k];
    }
    return $d;
}

function _cfos4r_find_device($devices,$devId){
    foreach ($devices as $d) {
        if (!is_array($d)) continue;
        $id1 = strval(_cfos4r_get($d,'dev_id',''));
        $id2 = strval(_cfos4r_get($d,'id',''));
        if ($id1 === $devId || $id2 === $devId) return $d;
    }
    return null;
}

function _cfos30_exec_set_changed($id,$out,$val){
    $json=logic_getVar($id,100);
    $cache=is_string($json) && $json!=='' ? json_decode($json,true) : array();
    if(!is_array($cache)) $cache=array();
    $key=strval($out);
    if(!array_key_exists($key,$cache) || strval($cache[$key])!==strval($val)){
        logic_setOutput($id,$out,$val);
        $cache[$key]=$val;
        logic_setVar($id,100,json_encode($cache));
    }
}

function _cfos4r_set_device($id,$baseOut,$devId,$device){
    $set = function($idx, $val) use ($id) { _cfos30_exec_set_changed($id, $idx, $val); };

    $set($baseOut,0);
    $set($baseOut+1,$devId);
    $set($baseOut+2,'');
    $set($baseOut+3,'');
    $set($baseOut+4,0);
    $set($baseOut+5,0);
    $set($baseOut+6,0);
    $set($baseOut+7,'');
    $set($baseOut+8,0);
    $set($baseOut+9,'{}');

    if ($devId === '' || !is_array($device)) return;

    $enabled = _cfos4r_first($device,array('device_enabled','charging_enabled','enabled'),false);
    $status = _cfos4r_first($device,array('state','status'),'');

    $set($baseOut,1);
    $set($baseOut+1,strval(_cfos4r_first($device,array('dev_id','id'),$devId)));
    $set($baseOut+2,strval(_cfos4r_get($device,'name','')));
    $set($baseOut+3,strval(_cfos4r_get($device,'model','')));
    $set($baseOut+4,floatval(_cfos4r_first($device,array('power_w','cur_power','cur_charging_power','power'),0)));
    $set($baseOut+5,floatval(_cfos4r_first($device,array('total_energy','energy_wh','import_wh','export_wh'),0)));
    $set($baseOut+6,floatval(_cfos4r_first($device,array('current','cur','charging_cur','max_charging_cur'),0)));
    $set($baseOut+7,strval($status));
    $set($baseOut+8,_cfos4r_bool($enabled));
    $set($baseOut+9,json_encode($device, JSON_UNESCAPED_UNICODE));
}

if (!($E=logic_getInputsQueued($id))) {
    $E=logic_getInputs($id);
}
if ($E) {
    $set = function($idx, $val) use ($id) { _cfos30_exec_set_changed($id, $idx, $val); };

    if (intval($E[6]['value'])!=1) return;

    $host = trim((string)$E[1]['value']);
    $port = intval($E[2]['value']); if ($port <= 0) $port = 80;
    $https = intval($E[3]['value']) ? 1 : 0;
    $user = (string)$E[4]['value'];
    $pass = (string)$E[5]['value'];
    $debugRaw = intval($E[12]['value'])==1;
    $sslVerify = intval($E[13]['value'])==1;

    $ids = array(
        trim((string)$E[8]['value']),
        trim((string)$E[9]['value']),
        trim((string)$E[10]['value']),
        trim((string)$E[11]['value'])
    );

    $set(1,0); $set(2,0); $set(3,''); $set(4,time()); $set(5,''); $set(6,0); $set(7,''); $set(8,'queued');
    _cfos4r_set_device($id,10,$ids[0],null);
    _cfos4r_set_device($id,20,$ids[1],null);
    _cfos4r_set_device($id,30,$ids[2],null);
    _cfos4r_set_device($id,40,$ids[3],null);

    $now=time();
    $retryTs=intval(logic_getVar($id,90));
    if($retryTs>0 && $now<$retryTs){
        $set(3,'offline (cooldown bis '.date('H:i:s',$retryTs).')');
        $set(8,'offline cooldown');
    }
    else if ($host === '') { $set(3, 'Host leer'); $set(8,'fehler: Host leer'); }
    else if (!function_exists('curl_init')) { $set(3, 'curl fehlt'); $set(8,'fehler: curl fehlt'); }
    else {
        $scheme = $https ? 'https' : 'http';
        $url = $scheme.'://'.$host.':'.$port.'/cnf?cmd=get_dev_info';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        if ($user !== '') curl_setopt($ch, CURLOPT_USERPWD, $user.':'.$pass);
        if ($https) { curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslVerify); curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $sslVerify?2:0); }

        $resp = curl_exec($ch);
        if ($resp === false) {
            $set(3, 'curl: '.curl_error($ch));
            $set(8,'fehler: curl');
            curl_close($ch);
            logic_setVar($id,90,$now+15);
        }
        else {
            $http = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
            curl_close($ch);
            $set(2, $http);
            if ($debugRaw) $set(5, $resp);

            $data = json_decode($resp, true);
            if (!is_array($data)) { $set(3, 'JSON parse fehlgeschlagen'); $set(8,'fehler: JSON'); }
            else {
                $devices = (isset($data['devices']) && is_array($data['devices'])) ? $data['devices'] : array();
                $found = array();
                for ($i=0; $i<4; $i++) {
                    $dev = ($ids[$i] === '') ? null : _cfos4r_find_device($devices,$ids[$i]);
                    if (is_array($dev)) $found[] = strval(_cfos4r_first($dev,array('dev_id','id'),$ids[$i]));
                    _cfos4r_set_device($id,10+($i*10),$ids[$i],$dev);
                }

                $set(6,count($devices));
                $set(7,implode(',',$found));
                $set(1,1);
                $set(3,'');
                $set(8,'ok: '.count($found).' von '.count(array_filter($ids)).' IDs gefunden');
                logic_setVar($id,90,0);
            }
        }
    }
}
?>
###[/EXEC]###
