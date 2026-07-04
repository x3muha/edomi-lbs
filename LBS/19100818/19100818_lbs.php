###[DEF]###
[name = cFos HTTP Zähler 1.0]
[version = 1.0]

[e#1 = IP/Host (z.B. 10.77.77.4)]
[e#2 = User]
[e#3 = Passwort]
[e#4 = ID (z.B. M4)]
[e#5 = Model (z.B. PVErzeugung)]
[e#6 TRIGGER = power_w]
[e#7 = Aktiv 1/0 #init=1]
[e#8 = SSL pruefen 1/0 #init=0]

[a#1 = OK 1/0]
[a#2 = Fehlertext]
[a#3 = HTTP Statuscode]
[a#4 = Gesendete URL]
[a#5 = Gesendetes JSON]

[v#91 = 0] Pending
[v#100 = ] letzte Ausgaenge JSON
###[/DEF]###

###[HELP]###
Version: 1.0

cFos HTTP Zähler Sender

Zweck:
- Sendet model + power_w per POST an cFos set_ajax_meter.

Request:
- URL: /cnf?cmd=set_ajax_meter&dev_id=<ID>
- Body: {"model":"...","power_w":...}

Hinweise:
- ID direkt als Mx eintragen (z. B. M4).
- Negative Leistung erlaubt.
- Versand bei Trigger auf E6 oder Konfig-Änderung.
- E8=1 aktiviert SSL-Zertifikatspruefung bei HTTPS; Standard 0 fuer lokale/self-signed cFos-Installationen.
- Der HTTP-Request laeuft im EXEC-Teil, damit ein nicht erreichbarer cFos die Logik nicht blockiert.
- Ausgaenge werden nur bei Wertwechsel geschrieben. Bei Aktiv=0 bleiben die letzten Ausgangswerte stehen.

###[/HELP]###

###[LBS]###
<?php
function _cfos18_lbs_set_changed($id,$out,$val){
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

  $pending=intval(logic_getVar($id,91))==1;
  if($E[1]['refresh']!=1 && $E[2]['refresh']!=1 && $E[3]['refresh']!=1 && $E[4]['refresh']!=1 && $E[5]['refresh']!=1 && $E[6]['refresh']!=1 && $E[7]['refresh']!=1 && $E[8]['refresh']!=1 && !(intval(logic_getState($id))==1 && $pending)) return;

  $set=function($o,$v) use($id){ _cfos18_lbs_set_changed($id,$o,$v); };

  if(intval($E[7]['value'])!=1){ logic_setState($id,0); logic_setVar($id,91,0); return; }

  logic_setInputsQueued($id,$E);
  if(logic_getStateExec($id)==0){
    logic_setVar($id,91,0);
    logic_setState($id,0);
    $set(1,0);
    $set(2,'queued');
    logic_callExec(LBSID,$id,false);
  } else {
    logic_setVar($id,91,1);
    logic_setState($id,1,1000);
  }
}
?>
###[/LBS]###

###[EXEC]###
<?php
require(dirname(__FILE__)."/../../../../main/include/php/incl_lbsexec.php");
set_time_limit(10);
sql_connect();

function _cfos1_num($v,$d=0.0){ return is_numeric($v)?floatval($v):$d; }
function _cfos18_exec_set_changed($id,$out,$val){
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
  $set=function($o,$v) use($id){ _cfos18_exec_set_changed($id,$o,$v); };

  if(intval($E[7]['value'])!=1) return;

  $set(1,0); $set(2,''); $set(3,0);

  $host=trim((string)$E[1]['value']);
  $user=trim((string)$E[2]['value']);
  $pass=(string)$E[3]['value'];
  $sslVerify=intval($E[8]['value'])==1;
  $devId=trim((string)$E[4]['value']);
  $model=trim((string)$E[5]['value']);
  $power=_cfos1_num($E[6]['value'],0);

  if($host===''){ $set(2,'IP/Host fehlt'); return; }
  if($user===''){ $set(2,'User fehlt'); return; }
  if($devId===''){ $set(2,'ID fehlt'); return; }
  if($model==='') $model='HTTPMeter';
  if(!function_exists('curl_init')){ $set(2,'curl fehlt'); return; }

  if(strpos($host,'http://')!==0 && strpos($host,'https://')!==0) $host='http://'.$host;
  $url=rtrim($host,'/').'/cnf?cmd=set_ajax_meter&dev_id='.rawurlencode($devId);

  $payload=array(
    'model'=>$model,
    'power_w'=>$power
  );
  $json=json_encode($payload, JSON_UNESCAPED_UNICODE);

  $set(4,$url);
  $set(5,$json);

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
    $set(2,'curl: '.curl_error($ch));
    curl_close($ch);
    return;
  }

  $code=intval(curl_getinfo($ch,CURLINFO_HTTP_CODE));
  curl_close($ch);

  $set(3,$code);

  if($code>=200 && $code<300){
    $set(1,1);
    $set(2,'ok');
  } else {
    $set(1,0);
    $set(2,'HTTP '.$code.' Antwort: '.substr((string)$resp,0,200));
  }
}
?>
###[/EXEC]###
