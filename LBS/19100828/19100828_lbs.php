###[DEF]###
[name = cFos Variablen Reader 4fach 1.0]
[version = 1.0]

[e#1 = IP/Host (z.B. 10.77.77.4)]
[e#2 = User]
[e#3 = Passwort]

[e#4 = Varname 1]
[e#5 = Varname 2]
[e#6 = Varname 3]
[e#7 = Varname 4]

[e#8 TRIGGER = Lesen]
[e#9 = Aktiv 1/0 #init=1]
[e#10 = Debug 1/0 #init=0]
[e#11 = SSL pruefen 1/0 #init=0]

[a#1 = OK gesamt 1/0]
[a#2 = Fehlertext gesamt]
[a#3 = Status Text]
[a#4 = HTTP Statuscode]
[a#5 = Gesendete URL]
[a#6 = Antwort JSON]
[a#7 = Wert 1]
[a#8 = Wert 2]
[a#9 = Wert 3]
[a#10 = Wert 4]
[a#11 = Anzahl gefunden]
[a#12 = Variablen Text]
[a#13 = Zeitstempel]

[v#91 = 0] Pending
[v#100 = ] letzte Ausgaenge JSON
###[/DEF]###

###[HELP]###
Version: 1.0

cFos Variablen Reader 4fach

Zweck:
- Liest bis zu 4 Charging-Manager-Variablen aus cFos.
- E8 triggert den Lesevorgang. Konfig-Aenderungen alleine starten keinen HTTP-Request.

Request:
- GET /cnf?cmd=get_cm_vars
- Antwort ist ein JSON Objekt mit den Charging-Manager-Variablen.

Hinweise:
- User/Pass werden fuer HTTP Basic Auth genutzt, wenn User gesetzt ist.
- Leere Variablennamen werden uebersprungen.
- Pro Variable wird bevorzugt der berechnete Wert ausgegeben; falls nicht vorhanden, die Formel/Konstante.
- E11=1 aktiviert SSL-Zertifikatspruefung bei HTTPS; Standard 0 fuer lokale/self-signed cFos-Installationen.
- Passwort wird nicht auf Ausgaenge geschrieben.
- Der HTTP-Request laeuft im EXEC-Teil, damit ein nicht erreichbarer cFos die Logik nicht blockiert.
- Ausgaenge werden nur bei Wertwechsel geschrieben. Bei Aktiv=0 bleiben die letzten Ausgangswerte stehen.
###[/HELP]###

###[LBS]###
<?php
function _cfos28_lbs_set_changed($id,$out,$val){
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
  if($E[8]['refresh']!=1 && !(intval(logic_getState($id))==1 && $pending)) return;

  $set=function($o,$v) use($id){ _cfos28_lbs_set_changed($id,$o,$v); };

  if(intval($E[9]['value'])!=1){
    logic_setState($id,0);
    logic_setVar($id,91,0);
    return;
  }

  logic_setInputsQueued($id,$E);
  if(logic_getStateExec($id)==0){
    logic_setVar($id,91,0);
    logic_setState($id,0);
    $set(1,0);
    $set(2,'queued');
    $set(3,'queued');
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
set_time_limit(15);
sql_connect();

function _cfosvarr_short($s,$len=500){
  $s=str_replace(array("\r","\n","\t"),' ',(string)$s);
  return strlen($s)>$len ? substr($s,0,$len).'...' : $s;
}

function _cfosvarr_scalar($v){
  if(is_bool($v)) return $v ? 1 : 0;
  if(is_null($v)) return '';
  if(is_scalar($v)) return (string)$v;
  return json_encode($v, JSON_UNESCAPED_UNICODE);
}

function _cfosvarr_pick_value($entry){
  foreach(array('value','val','result','num','number','expr') as $k){
    if(is_array($entry) && array_key_exists($k,$entry)) return _cfosvarr_scalar($entry[$k]);
  }
  return _cfosvarr_scalar($entry);
}

function _cfosvarr_index_vars($data){
  $idx=array();
  if(is_array($data) && isset($data['vars']) && is_array($data['vars'])){
    foreach($data['vars'] as $v){
      if(is_array($v) && isset($v['name'])){
        $idx[(string)$v['name']]=$v;
      }
    }
  }
  if(is_array($data)){
    foreach($data as $k=>$v){
      if($k==='vars') continue;
      if(is_array($v) && isset($v['name'])){
        $idx[(string)$v['name']]=$v;
      } else if(!is_array($v)) {
        $idx[(string)$k]=$v;
      }
    }
  }
  return $idx;
}
function _cfos28_exec_set_changed($id,$out,$val){
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
  $set=function($o,$v) use($id){ _cfos28_exec_set_changed($id,$o,$v); };

  if(intval($E[9]['value'])!=1){
    return;
  }

  $host=trim((string)$E[1]['value']);
  $user=trim((string)$E[2]['value']);
  $pass=(string)$E[3]['value'];
  $debug=intval($E[10]['value'])==1;
  $sslVerify=intval($E[11]['value'])==1;

  $set(1,0); $set(2,''); $set(3,''); $set(4,0); $set(5,''); $set(6,''); $set(7,''); $set(8,''); $set(9,''); $set(10,''); $set(11,0); $set(12,''); $set(13,date('Y-m-d H:i:s'));

  if($host===''){ $set(2,'IP/Host fehlt'); $set(3,'fehler: IP/Host fehlt'); return; }
  if(!function_exists('curl_init')){ $set(2,'curl fehlt'); $set(3,'fehler: curl fehlt'); return; }

  $names=array(
    1=>trim((string)$E[4]['value']),
    2=>trim((string)$E[5]['value']),
    3=>trim((string)$E[6]['value']),
    4=>trim((string)$E[7]['value'])
  );

  if(strpos($host,'http://')!==0 && strpos($host,'https://')!==0) $host='http://'.$host;
  $url=rtrim($host,'/').'/cnf?cmd=get_cm_vars';

  $set(5,$url);

  $ch=curl_init();
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,2);
  curl_setopt($ch,CURLOPT_TIMEOUT,5);
  curl_setopt($ch,CURLOPT_HTTPGET,true);
  curl_setopt($ch,CURLOPT_HTTPHEADER,array('Accept: application/json'));
  if($user!=='') curl_setopt($ch,CURLOPT_USERPWD,$user.':'.$pass);
  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,$sslVerify);
  curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,$sslVerify?2:0);

  $resp=curl_exec($ch);
  if($resp===false){
    $msg='curl: '.curl_error($ch);
    $set(2,$msg);
    $set(3,'fehler: '.$msg);
    curl_close($ch);
    return;
  }

  $code=intval(curl_getinfo($ch,CURLINFO_HTTP_CODE));
  curl_close($ch);

  $set(4,$code);
  $set(6,$debug ? $resp : _cfosvarr_short($resp,500));

  if(!($code>=200 && $code<300)){
    $msg='HTTP '.$code.' Antwort: '._cfosvarr_short($resp,200);
    $set(2,$msg);
    $set(3,'fehler: '.$msg);
    return;
  }

  $data=json_decode($resp,true);
  if(!is_array($data)){
    $msg='ungueltiges JSON: '._cfosvarr_short(json_last_error_msg(),120);
    $set(2,$msg);
    $set(3,'fehler: '.$msg);
    return;
  }

  $idx=_cfosvarr_index_vars($data);
  $found=0;
  $texts=array();
  $missing=array();

  foreach($names as $nr=>$name){
    $out=6+$nr;
    if($name===''){
      $missing[]='V'.$nr.' skip (Name leer)';
      continue;
    }
    if(array_key_exists($name,$idx)){
      $value=_cfosvarr_pick_value($idx[$name]);
      $set($out,$value);
      $texts[]=$name.'='.$value;
      $found++;
    } else {
      $set($out,'');
      $missing[]=$name.' nicht gefunden';
    }
  }

  $set(11,$found);
  $set(12,implode(' | ',$texts));

  if($found>0){
    $status='ok: '.$found.' Variablen gelesen';
    if(count($texts)>0) $status.=' ('.implode(' | ',$texts).')';
    if(count($missing)>0) $status.=' | '.implode(' | ',$missing);
    $set(1,1);
    $set(2,'');
    $set(3,_cfosvarr_short($status,500));
  } else {
    $msg='keine Variable gefunden';
    if(count($missing)>0) $msg.=': '.implode(' | ',$missing);
    $set(1,0);
    $set(2,$msg);
    $set(3,'fehler: '.$msg);
  }
}
?>
###[/EXEC]###
