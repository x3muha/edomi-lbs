###[DEF]###
[name = cFos Switch Writer 4fach 1.0]
[version = 1.0]

[e#1 = IP/Host (z.B. 10.77.77.4)]
[e#2 = User]
[e#3 = Passwort]

[e#4 = Varname 1]
[e#5 TRIGGER = Wert 1 (0/1)]
[e#6 = Varname 2]
[e#7 TRIGGER = Wert 2 (0/1)]
[e#8 = Varname 3]
[e#9 TRIGGER = Wert 3 (0/1)]
[e#10 = Varname 4]
[e#11 TRIGGER = Wert 4 (0/1)]

[e#12 = Aktiv 1/0 #init=1]
[e#13 = Debug 1/0 #init=0]
[e#14 = SSL pruefen 1/0 #init=0]

[a#1 = OK gesamt 1/0]
[a#2 = Fehlertext gesamt]
[a#3 = Status Text]
[a#4 = HTTP1]
[a#5 = HTTP2]
[a#6 = HTTP3]
[a#7 = HTTP4]
[a#8 = Letzte URL]
[a#9 = Antwort kurz]
[a#10 = Anzahl gesendet]
[a#11 = Variablen Text]
[a#12 = Zeitstempel]

[v#91 = 0] Pending
[v#100 = ] letzte Ausgaenge JSON
###[/DEF]###

###[HELP]###
Version: 1.0

cFos Switch Writer 4fach

Zweck:
- Schreibt bis zu 4 als Switch/Bool deklarierte cFos Charging-Manager-Variablen.
- Gemeinsame IP/User/Pass-Konfiguration, Versand bei Trigger auf einem Wert oder Konfig-Aenderung.

Request je Variable:
- GET /cnf?cmd=set_cm_var&name=<Varname>&val=<0|1>

Hinweise:
- Leere Variablennamen oder Werte werden uebersprungen; 0 ist ein gueltiger Wert und wird gesendet.
- Akzeptierte Werte: 0/1, true/false, on/off, ein/aus, ja/nein.
- E14=1 aktiviert SSL-Zertifikatspruefung bei HTTPS; Standard 0 fuer lokale/self-signed cFos-Installationen.
- Passwort wird nicht auf Ausgaenge geschrieben.
- Der HTTP-Request laeuft im EXEC-Teil, damit ein nicht erreichbarer cFos die Logik nicht blockiert.
- Ausgaenge werden nur bei Wertwechsel geschrieben. Bei Aktiv=0 bleiben die letzten Ausgangswerte stehen.
###[/HELP]###

###[LBS]###
<?php
function _cfos29_lbs_set_changed($id,$out,$val){
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

function LB_LBSID($id){
  if(!($E=logic_getInputs($id))) return;

  if($E[1]['refresh']!=1 && $E[2]['refresh']!=1 && $E[3]['refresh']!=1 &&
     $E[4]['refresh']!=1 && $E[5]['refresh']!=1 && $E[6]['refresh']!=1 &&
     $E[7]['refresh']!=1 && $E[8]['refresh']!=1 && $E[9]['refresh']!=1 &&
     $E[10]['refresh']!=1 && $E[11]['refresh']!=1 && $E[12]['refresh']!=1 &&
     $E[13]['refresh']!=1 && $E[14]['refresh']!=1 && intval(logic_getVar($id,91))!=1) return;

  $set=function($o,$v) use($id){ _cfos29_lbs_set_changed($id,$o,$v); };

  if(intval($E[12]['value'])!=1){
    logic_setVar($id,91,0);
    logic_setState($id,0);
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

function _cfossw_short($s,$len=240){
  $s=str_replace(array("\r","\n","\t"),' ',(string)$s);
  return strlen($s)>$len ? substr($s,0,$len).'...' : $s;
}

function _cfossw_bool($v,&$ok){
  $t=strtolower(trim((string)$v));
  $ok=true;
  if($t==='1' || $t==='true' || $t==='on' || $t==='ein' || $t==='ja' || $t==='yes') return '1';
  if($t==='0' || $t==='false' || $t==='off' || $t==='aus' || $t==='nein' || $t==='no') return '0';
  if(is_numeric($t)) return (floatval($t)!=0.0) ? '1' : '0';
  $ok=false;
  return '';
}

function _cfos29_exec_set_changed($id,$out,$val){
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

if(!($E=logic_getInputsQueued($id))) {
  $E=logic_getInputs($id);
}
if($E){
  $set=function($o,$v) use($id){ _cfos29_exec_set_changed($id,$o,$v); };

  if(intval($E[12]['value'])!=1){
    return;
  }

  $host=trim((string)$E[1]['value']);
  $user=trim((string)$E[2]['value']);
  $pass=(string)$E[3]['value'];
  $debug=intval($E[13]['value'])==1;
  $sslVerify=intval($E[14]['value'])==1;

  $set(1,0); $set(2,''); $set(3,''); $set(4,0); $set(5,0); $set(6,0); $set(7,0); $set(8,''); $set(9,''); $set(10,0); $set(11,''); $set(12,date('Y-m-d H:i:s'));

  if($host===''){ $set(2,'IP/Host fehlt'); $set(3,'fehler: IP/Host fehlt'); return; }
  if($user===''){ $set(2,'User fehlt'); $set(3,'fehler: User fehlt'); return; }
  if(!function_exists('curl_init')){ $set(2,'curl fehlt'); $set(3,'fehler: curl fehlt'); return; }

  $pairs=array(
    array('name'=>trim((string)$E[4]['value']),  'value'=>trim((string)$E[5]['value']),  'httpOut'=>4),
    array('name'=>trim((string)$E[6]['value']),  'value'=>trim((string)$E[7]['value']),  'httpOut'=>5),
    array('name'=>trim((string)$E[8]['value']),  'value'=>trim((string)$E[9]['value']),  'httpOut'=>6),
    array('name'=>trim((string)$E[10]['value']), 'value'=>trim((string)$E[11]['value']), 'httpOut'=>7)
  );

  if(strpos($host,'http://')!==0 && strpos($host,'https://')!==0) $host='http://'.$host;
  $base=rtrim($host,'/');

  $ok=true;
  $sent=0;
  $labels=array();
  $msgs=array();
  $lastResp='';

  foreach($pairs as $idx=>$p){
    $nr=$idx+1;
    if($p['name']===''){
      $msgs[]='V'.$nr.' skip (Name leer)';
      continue;
    }
    if(strlen($p['value'])==0){
      $msgs[]='V'.$nr.' skip (Wert leer)';
      continue;
    }

    $boolOk=true;
    $val=_cfossw_bool($p['value'],$boolOk);
    if(!$boolOk){
      $ok=false;
      $msgs[]='V'.$nr.' ungueltiger Bool-Wert: '.$p['value'];
      continue;
    }

    $url=$base.'/cnf?cmd=set_cm_var&name='.rawurlencode($p['name']).'&val='.$val;
    $labels[]=$p['name'].'='.$val;
    $set(8,$url);

    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,2);
    curl_setopt($ch,CURLOPT_TIMEOUT,5);
    curl_setopt($ch,CURLOPT_HTTPGET,true);
    curl_setopt($ch,CURLOPT_HTTPHEADER,array('Accept: application/json'));
    curl_setopt($ch,CURLOPT_USERPWD,$user.':'.$pass);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,$sslVerify);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,$sslVerify?2:0);

    $resp=curl_exec($ch);
    if($resp===false){
      $ok=false;
      $set($p['httpOut'],0);
      $msgs[]='V'.$nr.' curl: '.curl_error($ch);
      curl_close($ch);
      continue;
    }

    $code=intval(curl_getinfo($ch,CURLINFO_HTTP_CODE));
    curl_close($ch);

    $set($p['httpOut'],$code);
    $lastResp=(string)$resp;
    $sent++;

    if(!($code>=200 && $code<300)){
      $ok=false;
      $msgs[]='V'.$nr.' HTTP '.$code.' Antwort: '._cfossw_short($resp,160);
    }
  }

  $set(9,$debug ? $lastResp : _cfossw_short($lastResp,300));
  $set(10,$sent);
  $set(11,implode(' | ',$labels));

  if($sent==0){
    $msg='keine Switch-Variable gesendet';
    if(count($msgs)>0) $msg.=': '.implode(' | ',$msgs);
    $set(1,0);
    $set(2,$msg);
    $set(3,'fehler: '.$msg);
    return;
  }

  if($ok){
    $status='ok: '.$sent.' Switch-Variablen gesendet ('.implode(' | ',$labels).')';
    $set(1,1);
    $set(2,'');
    $set(3,_cfossw_short($status,500));
  } else {
    $msg='fehler: '.implode(' | ',$msgs);
    $set(1,0);
    $set(2,$msg);
    $set(3,_cfossw_short($msg,500));
  }
}
?>
###[/EXEC]###
