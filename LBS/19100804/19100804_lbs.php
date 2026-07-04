###[DEF]###
[name = cFos HTTP Reader 1.0]
[version = 1.0]

[e#1 = Host/IP]
[e#2 = Port]
[e#3 = HTTPS 0/1]
[e#4 = User]
[e#5 = Passwort]
[e#6 = Aktiv 1/0]
[e#7 = Intervall Sekunden]
[e#8 = SSL pruefen 1/0 #init=0]
[e#9 = Debug 0/1]

[a#1 = OK]
[a#2 = HTTP-Code]
[a#3 = Fehlertext]
[a#4 = Timestamp]
[a#5 = RAW JSON]
[a#10 = Lastmanagement aktiv]
[a#11 = Maximaler Gesamtstrom (mA)]
[a#12 = Reservestrom (mA)]
[a#13 = Überziehungsstrom (mA)]
[a#14 = Maximaler Gesamtstrom für Wallboxen (mA)]
[a#15 = Aktuelle Wallbox-Leistung gesamt (W)]
[a#16 = Verfügbare Wallbox-Leistung (W)]
[a#17 = Zykluszeit (ms)]
[a#18 = Version]
[a#19 = Shareware-Modus aktiv]
[a#30 = Wallbox Out (JSON)]

[v#1 = 0]
[v#90 = 0] Retry nach Fehler
[v#91 = 0] Pending
[v#100 = ] letzte Ausgaenge JSON
###[/DEF]###

###[HELP]###
Version: 1.0

cFos HTTP Reader (19100804)

Zweck:
- Zyklischer Reader für cFos /cnf?cmd=get_dev_info.
- Liefert globale Lastmanagement-Parameter und EVSE-Liste als JSON.

Betrieb:
- E6=1 startet den Zyklus, E6=0 stoppt.
- E7 Intervall in Sekunden.
- E1..E5 Host/Port/HTTPS/Auth.
- E8=1 aktiviert SSL-Zertifikatspruefung bei HTTPS; Standard 0 fuer lokale/self-signed cFos-Installationen.
- HTTP/curl laeuft im EXEC-Teil, damit ein nicht erreichbarer cFos die Logik nicht blockiert.
- Ausgaenge werden nur bei Wertwechsel geschrieben. Bei Aktiv=0 bleiben die letzten Ausgangswerte stehen.

Ausgänge:
- A10..A19: globale cFos-Parameter (Ströme, Leistung, Version, Shareware)
- A30: Wallboxen als JSON (E*-Devices), für Decoder 19100805.
- A1/A2/A3: OK/HTTP-Code/Fehlertext.

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
        $E[8]['refresh']==1 || $E[9]['refresh']==1) {
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

function _cfos_reader_bool($v){ return ($v===true || $v===1 || $v==='1' || $v==='true') ? 1 : 0; }
function _cfos_reader_get($a,$k,$d=null){ return (is_array($a) && array_key_exists($k,$a)) ? $a[$k] : $d; }
function _cfos04_exec_set_changed($id,$out,$val){
    static $cache=array();
    if(!isset($cache[$id])){
        $tmp=json_decode((string)logic_getVar($id,100),true);
        $cache[$id]=is_array($tmp)?$tmp:array();
    }
    $k=strval($out);
    if(!array_key_exists($k,$cache[$id]) || strval($cache[$id][$k])!==strval($val)){
        logic_setOutput($id,$out,$val);
        $cache[$id][$k]=$val;
        logic_setVar($id,100,json_encode($cache[$id]));
    }
}

if (!($E=logic_getInputsQueued($id))) {
    $E=logic_getInputs($id);
}
if ($E) {
    $set = function($idx, $val) use ($id) { _cfos04_exec_set_changed($id, $idx, $val); };

    if (intval($E[6]['value'])!=1) return;

    $host = trim((string)$E[1]['value']);
    $port = intval($E[2]['value']); if ($port <= 0) $port = 80;
    $https = intval($E[3]['value']) ? 1 : 0;
    $user = (string)$E[4]['value'];
    $pass = (string)$E[5]['value'];
    $sslVerify = intval($E[8]['value'])==1;

    $set(4,time());

    // Fehler-Cooldown: bei Nichterreichbarkeit nicht jeden Zyklus neu versuchen.
    $now=time();
    $retryTs=intval(logic_getVar($id,90));
    if($retryTs>0 && $now<$retryTs){
        $set(1,0); $set(3,'offline (cooldown bis '.date('H:i:s',$retryTs).')');
    }
    else if ($host === '') { $set(1,0); $set(3, 'Host leer'); }
    else if (!function_exists('curl_init')) { $set(1,0); $set(3, 'curl fehlt'); }
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
            $set(1,0);
            $set(3, 'curl: '.curl_error($ch));
            curl_close($ch);
            logic_setVar($id,90,$now+15);
        }
        else {
            $http = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
            curl_close($ch);
            $set(2, $http); $set(5, $resp);

            $data = json_decode($resp, true);
            if (!is_array($data)) { $set(1,0); $set(3, 'JSON parse fehlgeschlagen'); }
            else {
                $params = (isset($data['params']) && is_array($data['params'])) ? $data['params'] : array();
                $devices = (isset($data['devices']) && is_array($data['devices'])) ? $data['devices'] : array();

                $evses = array();
                foreach ($devices as $d) {
                    if (!is_array($d)) continue;
                    $did = (string)_cfos_reader_get($d,'dev_id','');
                    $isEvse = _cfos_reader_bool(_cfos_reader_get($d,'is_evse',false));
                    if ($isEvse || strpos($did,'E')===0) $evses[] = $d;
                }

                $set(10, _cfos_reader_bool(_cfos_reader_get($params,'lb_enabled',false)));
                $set(11, intval(_cfos_reader_get($params,'max_total_current',0)));
                $set(12, intval(_cfos_reader_get($params,'reserve_current',0)));
                $set(13, intval(_cfos_reader_get($params,'overdraft_cur',0)));
                $set(14, intval(_cfos_reader_get($params,'max_total_evse_current',0)));
                $set(15, intval(_cfos_reader_get($params,'cons_evse_power',0)));
                $set(16, intval(_cfos_reader_get($params,'avail_evse_power',0)));
                $set(17, intval(_cfos_reader_get($params,'cycle_time',0)));
                $set(18, (string)_cfos_reader_get($params,'version',''));
                $set(19, _cfos_reader_bool(_cfos_reader_get($params,'shareware_mode',false)));
                $set(30, json_encode($evses, JSON_UNESCAPED_UNICODE));
                $set(1,1); $set(3,'');
                logic_setVar($id,90,0);
            }
        }
    }
}
?>
###[/EXEC]###
