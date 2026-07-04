###[DEF]###
[name = cFos Variablen Writer 4fach 1.0]
[version = 1.0]

[e#1 = IP/Host (z.B. 10.77.77.4)]
[e#2 = User]
[e#3 = Passwort]

[e#4 = Varname 1]
[e#5 TRIGGER = Wert 1]
[e#6 = Varname 2]
[e#7 TRIGGER = Wert 2]
[e#8 = Varname 3]
[e#9 TRIGGER = Wert 3]
[e#10 = Varname 4]
[e#11 TRIGGER = Wert 4]

[e#12 = Aktiv 1/0 #init=1]
[e#13 = tmp=1 nicht speichern #init=1]
[e#14 = Debug 1/0 #init=0]
[e#15 = SSL pruefen 1/0 #init=0]

[a#1 = OK gesamt 1/0]
[a#2 = Fehlertext gesamt]
[a#3 = Status Text]
[a#4 = HTTP Statuscode]
[a#5 = Gesendete URL]
[a#6 = Gesendetes JSON]
[a#7 = Antwort kurz]
[a#8 = Anzahl gesendet]
[a#9 = Variablen Text]
[a#10 = Zeitstempel]

[v#91 = 0] Pending
[v#100 = ] letzte Ausgaenge JSON
###[/DEF]###

###[HELP]###
Version: 1.0

cFos Variablen Writer 4fach

Zweck:
- Schreibt bis zu 4 Charging-Manager-Variablen in cFos.
- Gemeinsame IP/User/Pass-Konfiguration, Versand bei Trigger auf einem Wert oder Konfig-Aenderung.

Request:
- POST /cnf?cmd=set_cm_vars
- Body: {"vars":[{"name":"var1","expr":123},{"name":"var2","expr":"text"}]}
- E13=1 fuegt tmp=1 an und schont den Flash-Speicher bei haeufigen Updates.

Hinweise:
- Leere Variablennamen oder Werte werden uebersprungen; 0 ist ein gueltiger Wert und wird gesendet.
- Zahlen werden numerisch gesendet, Texte/Formeln als String.
- E15=1 aktiviert SSL-Zertifikatspruefung bei HTTPS; Standard 0 fuer lokale/self-signed cFos-Installationen.
- Passwort wird nicht auf Ausgaenge geschrieben.
- Der HTTP-Request laeuft im EXEC-Teil, damit ein nicht erreichbarer cFos die Logik nicht blockiert.
- Ausgaenge werden nur bei Wertwechsel geschrieben. Bei Aktiv=0 bleiben die letzten Ausgangswerte stehen.
###[/HELP]###

###[LBS]###
<?php
function _cfos27_lbs_set_changed($id,$out,$val){
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
  if($E[1]['refresh']!=1 && $E[2]['refresh']!=1 && $E[3]['refresh']!=1 &&
     $E[4]['refresh']!=1 && $E[5]['refresh']!=1 && $E[6]['refresh']!=1 &&
     $E[7]['refresh']!=1 && $E[8]['refresh']!=1 && $E[9]['refresh']!=1 &&
     $E[10]['refresh']!=1 && $E[11]['refresh']!=1 && $E[12]['refresh']!=1 &&
     $E[13]['refresh']!=1 && $E[14]['refresh']!=1 && $E[15]['refresh']!=1 &&
     !(intval(logic_getState($id))==1 && $pending)) return;

  $set=function($o,$v) use($id){ _cfos27_lbs_set_changed($id,$o,$v); };

  if(intval($E[12]['value'])!=1){
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

function _cfosvar_short($s,$len=240){
  $s=str_replace(array("\r","\n","\t"),' ',(string)$s);
  return strlen($s)>$len ? substr($s,0,$len).'...' : $s;
}

function _cfosvar_expr($v){
  $t=trim((string)$v);
  $n=str_replace(',','.',$t);
  if($t!=='' && is_numeric($n)) return $n+0;
  return $t;
}
function _cfos27_exec_set_changed($id,$out,$val){
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
  $set=function($o,$v) use($id){ _cfos27_exec_set_changed($id,$o,$v); };

  if(intval($E[12]['value'])!=1){
    return;
  }

  $host=trim((string)$E[1]['value']);
  $user=trim((string)$E[2]['value']);
  $pass=(string)$E[3]['value'];
  $tmp=intval($E[13]['value'])==1;
  $debug=intval($E[14]['value'])==1;
  $sslVerify=intval($E[15]['value'])==1;

  $set(1,0); $set(2,''); $set(3,''); $set(4,0); $set(5,''); $set(6,''); $set(7,''); $set(8,0); $set(9,''); $set(10,date('Y-m-d H:i:s'));

  if($host===''){ $set(2,'IP/Host fehlt'); $set(3,'fehler: IP/Host fehlt'); return; }
  if($user===''){ $set(2,'User fehlt'); $set(3,'fehler: User fehlt'); return; }
  if(!function_exists('curl_init')){ $set(2,'curl fehlt'); $set(3,'fehler: curl fehlt'); return; }

  $pairs=array(
    array('name'=>trim((string)$E[4]['value']),  'value'=>trim((string)$E[5]['value'])),
    array('name'=>trim((string)$E[6]['value']),  'value'=>trim((string)$E[7]['value'])),
    array('name'=>trim((string)$E[8]['value']),  'value'=>trim((string)$E[9]['value'])),
    array('name'=>trim((string)$E[10]['value']), 'value'=>trim((string)$E[11]['value']))
  );

  $vars=array();
  $labels=array();
  $skips=array();
  foreach($pairs as $idx=>$p){
    $nr=$idx+1;
    if($p['name']===''){
      $skips[]='V'.$nr.' skip (Name leer)';
      continue;
    }
    if(strlen($p['value'])==0){
      $skips[]='V'.$nr.' skip (Wert leer)';
      continue;
    }
    $expr=_cfosvar_expr($p['value']);
    $vars[]=array('name'=>$p['name'],'expr'=>$expr);
    $labels[]=$p['name'].'='.$p['value'];
  }

  if(count($vars)==0){
    $msg='keine Variable gesendet (Namen/Werte leer)';
    if(count($skips)>0) $msg.=': '.implode(' | ',$skips);
    $set(2,$msg);
    $set(3,'fehler: '.$msg);
    $set(9,implode(' | ',$skips));
    return;
  }

  if(strpos($host,'http://')!==0 && strpos($host,'https://')!==0) $host='http://'.$host;
  $url=rtrim($host,'/').'/cnf?cmd=set_cm_vars'.($tmp?'&tmp=1':'');
  $payload=array('vars'=>$vars);
  $json=json_encode($payload, JSON_UNESCAPED_UNICODE);
  $varText=implode(' | ',$labels);

  $set(5,$url);
  $set(6,$debug ? $json : _cfosvar_short($json,300));
  $set(8,count($vars));
  $set(9,$varText);

  $ch=curl_init();
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,2);
  curl_setopt($ch,CURLOPT_TIMEOUT,5);
  curl_setopt($ch,CURLOPT_POST,true);
  curl_setopt($ch,CURLOPT_POSTFIELDS,$json);
  curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-Type: application/json','Accept: application/json'));
  curl_setopt($ch,CURLOPT_USERPWD,$user.':'.$pass);
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
  $set(7,_cfosvar_short($resp,300));

  if($code>=200 && $code<300){
    $status='ok: '.count($vars).' Variablen gesendet ('.$varText.')';
    if(count($skips)>0) $status.=' | '.implode(' | ',$skips);
    $set(1,1);
    $set(2,'');
    $set(3,_cfosvar_short($status,500));
  } else {
    $msg='HTTP '.$code.' Antwort: '._cfosvar_short($resp,200);
    $set(1,0);
    $set(2,$msg);
    $set(3,'fehler: '.$msg);
  }
}
?>
###[/EXEC]###
