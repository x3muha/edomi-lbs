###[DEF]###
[name = cFos HTTP Zähler 4fach 1.0]
[version = 1.0]

[e#1 = IP/Host (z.B. 10.77.77.4)]
[e#2 = User]
[e#3 = Passwort]

[e#4 = ID1 (z.B. M4)]
[e#5 = Model1]
[e#6 TRIGGER = power_w1]

[e#7 = ID2]
[e#8 = Model2]
[e#9 TRIGGER = power_w2]

[e#10 = ID3]
[e#11 = Model3]
[e#12 TRIGGER = power_w3]

[e#13 = ID4]
[e#14 = Model4]
[e#15 TRIGGER = power_w4]
[e#16 = Aktiv 1/0 #init=1]
[e#17 = SSL pruefen 1/0 #init=0]
[e#18 = Senden 1=zyklisch 2=Aenderung #init=1]
[e#19 = Zykluszeit Sekunden #init=2]

[a#1 = OK gesamt 1/0]
[a#2 = Fehlertext gesamt]
[a#3 = HTTP1]
[a#4 = HTTP2]
[a#5 = HTTP3]
[a#6 = HTTP4]
[a#7 = JSON1]
[a#8 = JSON2]
[a#9 = JSON3]
[a#10 = JSON4]
[a#11 = Status Text]
[a#12 = Versandart]
[a#13 = Zeitstempel]

[v#1 = 0] Zyklus aktiv
[v#91 = 0] Pending
[v#100 = ] letzte Ausgaenge JSON
###[/DEF]###

###[HELP]###
Version: 1.0

cFos HTTP Zaehler 4fach

Zweck:
- Sendet 4 cFos-Zaehler mit nur einer gemeinsamen Host/User/Pass-Konfiguration.
- Sendet immer alle konfigurierten Zaehler gemeinsam in einem EXEC-Lauf.

Eingaenge:
- E18=1 sendet zyklisch. E19 ist die Zykluszeit in Sekunden, Standard 2s.
- E18=2 sendet bei Aenderung auf E6/E9/E12/E15. Schnelle Aenderungen werden kurz gesammelt und dann gemeinsam gesendet.

Request je Zaehler:
- POST /cnf?cmd=set_ajax_meter&dev_id=<IDx>
- Body: {"model":"...","power_w":...}

Hinweise:
- Negative power_w erlaubt.
- Leere ID wird uebersprungen.
- E17=1 aktiviert SSL-Zertifikatspruefung bei HTTPS; Standard 0 fuer lokale/self-signed cFos-Installationen.
- A1=1 nur wenn alle gesendeten Zaehler HTTP 2xx liefern.
- Der HTTP-Request laeuft im EXEC-Teil, damit ein nicht erreichbarer cFos die Logik nicht blockiert.
- Ausgaenge werden nur bei Wertwechsel geschrieben. Bei Aktiv=0 bleiben die letzten Ausgangswerte stehen.
###[/HELP]###

###[LBS]###
<?php
function _cfos21_lbs_set_changed($id,$out,$val){
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

function LB_LBSID($id){
  if(!($E=logic_getInputs($id))) return;

  $enabled=intval($E[16]['value'])==1;
  $mode=intval($E[18]['value']);
  if($mode!==2) $mode=1;
  $interval=max(1,intval($E[19]['value']));
  $pending=intval(logic_getVar($id,91))==1;
  $cycleActive=intval(logic_getVar($id,1))==1;
  $timer=(intval(logic_getState($id))==1);
  $powerChanged=($E[6]['refresh']==1 || $E[9]['refresh']==1 || $E[12]['refresh']==1 || $E[15]['refresh']==1);
  $configChanged=($E[1]['refresh']==1 || $E[2]['refresh']==1 || $E[3]['refresh']==1 ||
     $E[4]['refresh']==1 || $E[5]['refresh']==1 || $E[7]['refresh']==1 ||
     $E[8]['refresh']==1 || $E[10]['refresh']==1 || $E[11]['refresh']==1 ||
     $E[13]['refresh']==1 || $E[14]['refresh']==1 || $E[16]['refresh']==1 ||
     $E[17]['refresh']==1 || $E[18]['refresh']==1 || $E[19]['refresh']==1);
  $needRun=false;

  $set=function($o,$v) use($id){ _cfos21_lbs_set_changed($id,$o,$v); };

  if(!$enabled){
    logic_setState($id,0);
    logic_setVar($id,1,0);
    logic_setVar($id,91,0);
    return;
  }

  if($mode==1){
    logic_setVar($id,1,1);
    if(($timer && !$powerChanged) || $configChanged || !$cycleActive || $pending) $needRun=true;
  } else {
    logic_setVar($id,1,0);
    if($timer && $pending) {
      $needRun=true;
    } else if($powerChanged || $configChanged || $pending) {
      logic_setInputsQueued($id,$E);
      logic_setVar($id,91,1);
      logic_setState($id,1,200);
      return;
    }
  }

  if(!$needRun) return;

  logic_setInputsQueued($id,$E);
  if(logic_getStateExec($id)==0){
    logic_setVar($id,91,0);
    $set(1,0);
    $set(2,'queued');
    $set(11,'queued');
    $set(12,$mode==1?'zyklisch':'aenderung');
    logic_callExec(LBSID,$id,false);
  } else {
    logic_setVar($id,91,1);
    logic_setState($id,1,1000);
    return;
  }

  if($mode==1) logic_setState($id,1,$interval*1000);
  else logic_setState($id,0);
}
?>
###[/LBS]###

###[EXEC]###
<?php
require(dirname(__FILE__)."/../../../../main/include/php/incl_lbsexec.php");
set_time_limit(25);
sql_connect();

function _cfos4_num($v,$d=0.0){ return is_numeric($v)?floatval($v):$d; }
function _cfos21_exec_set_changed($id,$out,$val){
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

if(!($E=logic_getInputsQueued($id))) {
  $E=logic_getInputs($id);
}
if($E){
  $set=function($o,$v) use($id){ _cfos21_exec_set_changed($id,$o,$v); };

  $host=trim((string)$E[1]['value']);
  $user=trim((string)$E[2]['value']);
  $pass=(string)$E[3]['value'];
  $sslVerify=intval($E[17]['value'])==1;
  $mode=intval($E[18]['value'])==2 ? 2 : 1;

  if(intval($E[16]['value'])!=1) return;

  $set(1,0); $set(2,'');
  $set(3,0); $set(4,0); $set(5,0); $set(6,0);
  $set(11,'sende');
  $set(12,$mode==1?'zyklisch':'aenderung');
  $set(13,date('Y-m-d H:i:s'));

  if($host===''){ $set(2,'IP/Host fehlt'); return; }
  if($user===''){ $set(2,'User fehlt'); return; }
  if(!function_exists('curl_init')){ $set(2,'curl fehlt'); return; }

  if(strpos($host,'http://')!==0 && strpos($host,'https://')!==0) $host='http://'.$host;
  $base=rtrim($host,'/');

  $meters=array(
    array('id'=>trim((string)$E[4]['value']),  'model'=>trim((string)$E[5]['value']),  'power'=>_cfos4_num($E[6]['value'],0),  'httpOut'=>3, 'jsonOut'=>7),
    array('id'=>trim((string)$E[7]['value']),  'model'=>trim((string)$E[8]['value']),  'power'=>_cfos4_num($E[9]['value'],0),  'httpOut'=>4, 'jsonOut'=>8),
    array('id'=>trim((string)$E[10]['value']), 'model'=>trim((string)$E[11]['value']), 'power'=>_cfos4_num($E[12]['value'],0), 'httpOut'=>5, 'jsonOut'=>9),
    array('id'=>trim((string)$E[13]['value']), 'model'=>trim((string)$E[14]['value']), 'power'=>_cfos4_num($E[15]['value'],0), 'httpOut'=>6, 'jsonOut'=>10)
  );

  $ok=true; $sent=0; $msgs=array();

  foreach($meters as $idx=>$m){
    if($m['id']==='') { $msgs[]='M'.($idx+1).' skip (ID leer)'; continue; }
    $model = ($m['model']==='') ? 'HTTPMeter' : $m['model'];
    $url=$base.'/cnf?cmd=set_ajax_meter&dev_id='.rawurlencode($m['id']);
    $payload=array('model'=>$model,'power_w'=>$m['power']);
    $json=json_encode($payload, JSON_UNESCAPED_UNICODE);
    $set($m['jsonOut'],$json);

    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,2);
    curl_setopt($ch,CURLOPT_TIMEOUT,5);
    curl_setopt($ch,CURLOPT_POST,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$json);
    curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
    curl_setopt($ch,CURLOPT_USERPWD,$user.':'.$pass);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,$sslVerify);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,$sslVerify?2:0);

    $resp=curl_exec($ch);
    if($resp===false){
      $ok=false;
      $set($m['httpOut'],0);
      $msgs[]='M'.($idx+1).' curl: '.curl_error($ch);
      curl_close($ch);
      continue;
    }

    $code=intval(curl_getinfo($ch,CURLINFO_HTTP_CODE));
    curl_close($ch);

    $set($m['httpOut'],$code);
    $sent++;

    if(!($code>=200 && $code<300)){
      $ok=false;
      $msgs[]='M'.($idx+1).' HTTP '.$code;
    }
  }

  if($sent==0){
    $set(1,0);
    $set(2,'kein Zaehler gesendet (alle IDs leer)');
    $set(11,'kein Zaehler gesendet');
    return;
  }

  $set(1,$ok?1:0);
  $set(2,$ok?'ok':'fehler: '.implode(' | ',$msgs));
  $set(11,$ok?'ok':'fehler');
}
?>
###[/EXEC]###
