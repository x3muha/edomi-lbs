###[DEF]###
[name = ESPHome Midea X3 1.0]
[titel = ESPHome Midea X3 Klima Status und Steuerung 1.0]
[version = 1.0]

[e#1 = ESPHome URL/IP #init=http://10.77.77.20]
[e#2 = User]
[e#3 = Passwort]
[e#4 TRIGGER = Lesen/Refresh]
[e#5 = Aktiv 1/0 #init=1]
[e#6 = Zyklus Sekunden #init=30]
[e#7 = HTTP Timeout Sekunden #init=5]
[e#8 = SSL pruefen 1/0 #init=0]
[e#9 = Debug 1/0 #init=0]

[e#10 TRIGGER = Solltemperatur C]
[e#11 TRIGGER = Kuehlen 1/0]
[e#12 TRIGGER = Heizen 1/0]
[e#13 TRIGGER = Auto 1/0]
[e#14 TRIGGER = Entfeuchten 1/0]
[e#15 TRIGGER = Lueften 1/0]
[e#16 TRIGGER = Aus 1/0]
[e#17 TRIGGER = Boost 1/0]
[e#18 TRIGGER = Eco 1/0]
[e#19 TRIGGER = Normal 1/0]
[e#20 TRIGGER = Fan Auto 1/0]
[e#21 TRIGGER = Fan Low 1/0]
[e#22 TRIGGER = Fan Medium 1/0]
[e#23 TRIGGER = Fan High 1/0]
[e#24 TRIGGER = Swing Aus 1/0]
[e#25 TRIGGER = Swing Vertikal 1/0]
[e#26 TRIGGER = Swing Horizontal 1/0]
[e#27 TRIGGER = Swing Beides 1/0]
[e#28 TRIGGER = Fresh 1/0]
[e#29 TRIGGER = Clean 1/0]
[e#30 TRIGGER = Clean Reset 1/0]
[e#31 TRIGGER = Clean Restore 1/0]
[e#32 TRIGGER = Clean Dauer Minuten]
[e#40 TRIGGER = Power Toggle 1/0]
[e#41 TRIGGER = Display Toggle 1/0]
[e#42 TRIGGER = Swing Step 1/0]
[e#43 TRIGGER = Follow Me 24C Test 1/0]

[a#1 = OK 1/0]
[a#2 = Fehlertext]
[a#3 = Status Text]
[a#4 = HTTP Status letzter Request]
[a#5 = Visu JSON]
[a#6 = Debug JSON]
[a#7 = Zeitstempel]
[a#8 = Last Command]
[a#9 = Command Status]
[a#10 = Status Solltemperatur C]
[a#11 = Status Kuehlen 1/0]
[a#12 = Status Heizen 1/0]
[a#13 = Status Auto 1/0]
[a#14 = Status Entfeuchten 1/0]
[a#15 = Status Lueften 1/0]
[a#16 = Status Aus 1/0]
[a#17 = Status Boost 1/0]
[a#18 = Status Eco 1/0]
[a#19 = Status Normal 1/0]
[a#20 = Status Fan Auto 1/0]
[a#21 = Status Fan Low 1/0]
[a#22 = Status Fan Medium 1/0]
[a#23 = Status Fan High 1/0]
[a#24 = Status Swing Aus 1/0]
[a#25 = Status Swing Vertikal 1/0]
[a#26 = Status Swing Horizontal 1/0]
[a#27 = Status Swing Beides 1/0]
[a#28 = Status Fresh 1/0]
[a#29 = Status Clean 1/0]
[a#30 = Clean State]
[a#31 = Status Clean Restore 1/0]
[a#32 = Status Clean Dauer Minuten]
[a#33 = Status Anlage An 1/0]
[a#34 = Modus Text]
[a#35 = Fan Mode Text]
[a#36 = Preset Text]
[a#37 = Swing Mode Text]
[a#38 = Isttemperatur C]
[a#39 = Clean Rest Minuten]
[a#40 = Last Control Source]
[a#41 = WiFi Signal dBm]
[a#42 = Uptime Sekunden]
[a#43 = Letzter HTTP Status]

[v#91 = 0] Pending
[v#100 = ] letzte Ausgaenge JSON
[v#101 = ] letzte direkte 1/0 Eingaben JSON
###[/DEF]###

###[HELP]###
Version: 1.0

ESPHome Midea X3 (19100839)

Zweck:
- Liest und steuert die Midea-X3-ESPHome-Firmware ueber die ESPHome Web API.
- Gibt Klima-, Fresh- und Clean-Status einzeln und als kompaktes JSON aus.
- Sendet Steuerbefehle an ESPHome ueber einzelne 1/0-Eingaenge fuer Modus, Preset, Fan, Swing, Fresh, Clean, Clean Restore und zusaetzliche Taster.

ESPHome-Seite:
- web_server muss aktiv sein.
- Erwartete Entity-Namen aus der Beispiel-YAML:
  - climate/Klimaanlage
  - switch/Midea Fresh
  - switch/Midea Clean
  - switch/Midea Clean Restore
  - number/Midea Clean Dauer
  - text_sensor/Midea Clean State
  - sensor/Midea Clean Remaining
  - text_sensor/Midea Last Control Source
  - sensor/Klima WiFi Signal
  - sensor/Klima Uptime
- Bei gesetzter Webserver-Authentifizierung E2/E3 belegen.

Eingaenge:
- E1: ESPHome URL oder IP, z.B. http://10.77.77.20 oder 10.77.77.20
- E2/E3: Webserver-User/Passwort
- E4: manueller Lese-Trigger
- E5: Aktiv
- E6: zyklisches Lesen in Sekunden, 0 = nur Trigger/Konfigaenderung
- E10: Solltemperatur in C.
- E11..E16: Betriebsart als 1/0-Eingang. 1 setzt den Modus, 0 loest keine Gegenaktion aus.
- E17..E19: Preset als 1/0-Eingang. 1 setzt Boost, Eco oder Normal.
- E20..E23: Fan Mode als 1/0-Eingang. 1 setzt Auto, Low, Medium oder High.
- E24..E27: Swing Mode als 1/0-Eingang. 1 setzt Aus, Vertikal, Horizontal oder Beides.
- E28: Fresh direkt setzen, 1=an, 0=aus.
- E29: Clean direkt setzen, 1=an, 0=aus.
- E30: Clean Reset, 1 loest Reset aus.
- E31: Clean Restore direkt setzen, 1=an, 0=aus.
- E32: Clean Dauer in Minuten.
- E40..E43: Power Toggle, Display Toggle, Swing Step, Follow Me 24C Test. 1 loest den Taster aus.

Ausgaenge:
- A1..A4: OK, Fehler, Status, letzter HTTP-Code.
- A5: kompaktes JSON fuer Visu/Weiterverarbeitung.
- A8/A9: letzter gesendeter Befehl und Befehlsstatus.
- A10..A32: Status passend zu den Eingangsnummern, z.B. E11 Kuehlen und A11 Status Kuehlen.
- A33..A43: zusaetzliche Status-/Diagnosewerte.

Hinweise:
- HTTP laeuft im EXEC-Teil.
- Bei jeder Eingangsaenderung wird sofort gearbeitet und nach ca. 2 Sekunden noch einmal gelesen.
- Ohne Eingangsaenderung gilt der normale Zyklus aus E6.
- Ausgaenge werden nur bei Wertwechsel geschrieben.
- ESPHome POSTs werden mit explizitem Content-Length gesendet, damit die Web API kein HTTP 411 liefert.
- Fresh-Status kommt aus der ESPHome-Entity Midea Fresh, die im Fork aus dem UART-Statusbit synchronisiert wird.
- Bei Fresh/Clean/Clean Restore wird ein erstes empfangenes 0 nicht als AUS gesendet. Erst nach einem gesehenen 1->0 Wechsel sendet der Baustein AUS.
- Clean-Status ist ESP-seitig geschaetzt; Clean Dauer und Clean Restore sind zur Laufzeit ueber E31/E32 steuerbar.
###[/HELP]###

###[LBS]###
<?php
function _midea39_lbs_set_changed($id,$out,$val){
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

  $changed=false;
  for($i=1;$i<=43;$i++){
    if(isset($E[$i]) && $E[$i]['refresh']==1){ $changed=true; break; }
  }

  $pending=intval(logic_getVar($id,91))==1;
  $timer=(intval(logic_getState($id))==1);
  if(!$changed && !$pending && !$timer) return;

  $set=function($o,$v) use($id){ _midea39_lbs_set_changed($id,$o,$v); };

  if(intval($E[5]['value'])!=1){
    logic_setState($id,0);
    logic_setVar($id,91,0);
    $set(3,'deaktiviert');
    return;
  }

  logic_setInputsQueued($id,$E);
  if(logic_getStateExec($id)==0){
    logic_setVar($id,91,0);
    $set(1,0);
    $set(2,'');
    $set(3,'queued');
    logic_callExec(LBSID,$id,false);
  } else {
    logic_setVar($id,91,1);
  }

  $cycle=max(0,intval($E[6]['value']));
  if($changed || $pending) logic_setState($id,1,2000);
  else if($cycle>0) logic_setState($id,1,$cycle*1000);
  else logic_setState($id,0);
}
?>
###[/LBS]###

###[EXEC]###
<?php
require(dirname(__FILE__)."/../../../../main/include/php/incl_lbsexec.php");
set_time_limit(25);
sql_connect();

function _midea39_short($s,$len=300){
  $s=str_replace(array("\r","\n","\t"),' ',(string)$s);
  return strlen($s)>$len ? substr($s,0,$len).'...' : $s;
}

function _midea39_bool($v){
  if(is_bool($v)) return $v ? 1 : 0;
  $s=strtolower(trim((string)$v));
  return ($s==='1' || $s==='on' || $s==='true' || $s==='yes') ? 1 : 0;
}

function _midea39_num($v,$default=''){
  if(is_numeric($v)) return $v+0;
  return $default;
}

function _midea39_base($host){
  $host=trim((string)$host);
  if($host!=='' && strpos($host,'http://')!==0 && strpos($host,'https://')!==0) $host='http://'.$host;
  return rtrim($host,'/');
}

function _midea39_path($base,$domain,$name,$action='',$query=''){
  $url=$base.'/'.rawurlencode($domain).'/'.rawurlencode($name);
  if($action!=='') $url.='/'.rawurlencode($action);
  if($query!=='') $url.='?'.$query;
  return $url;
}

function _midea39_curl($url,$method,$user,$pass,$timeout,$sslVerify,$body=null){
  $ch=curl_init();
  $headers=array('Accept: application/json');
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,min(5,max(1,$timeout)));
  curl_setopt($ch,CURLOPT_TIMEOUT,max(1,$timeout));
  if($user!==''){
    curl_setopt($ch,CURLOPT_USERPWD,$user.':'.$pass);
    if(defined('CURLAUTH_ANY')) curl_setopt($ch,CURLOPT_HTTPAUTH,CURLAUTH_ANY);
  }
  if(strpos($url,'https://')===0){
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,$sslVerify);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,$sslVerify?2:0);
  }
  if($method==='POST'){
    curl_setopt($ch,CURLOPT_POST,true);
    if($body===null) $body='';
    curl_setopt($ch,CURLOPT_POSTFIELDS,$body);
    $headers[]='Content-Length: '.strlen($body);
  }
  curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
  $resp=curl_exec($ch);
  $err=($resp===false)?curl_error($ch):'';
  $code=intval(curl_getinfo($ch,CURLINFO_HTTP_CODE));
  curl_close($ch);
  return array('ok'=>($resp!==false && $code>=200 && $code<300),'code'=>$code,'body'=>$resp,'error'=>$err,'url'=>$url);
}

function _midea39_get_entity($base,$domain,$name,$user,$pass,$timeout,$sslVerify,$detail=true){
  $url=_midea39_path($base,$domain,$name,'',$detail?'detail=all':'');
  $r=_midea39_curl($url,'GET',$user,$pass,$timeout,$sslVerify);
  if(!$r['ok']) return array('ok'=>false,'result'=>$r,'data'=>null);
  $data=json_decode((string)$r['body'],true);
  if(!is_array($data)) return array('ok'=>false,'result'=>$r,'data'=>null,'json_error'=>json_last_error_msg());
  return array('ok'=>true,'result'=>$r,'data'=>$data);
}

function _midea39_field($data,$key,$default=''){
  if(!is_array($data) || !array_key_exists($key,$data)) return $default;
  $v=$data[$key];
  if(is_array($v)){
    if(array_key_exists('value',$v)) return $v['value'];
    if(array_key_exists('state',$v)) return $v['state'];
    return $default;
  }
  return $v;
}

function _midea39_state($data,$default=''){
  if(!is_array($data)) return $default;
  if(array_key_exists('state',$data)) return $data['state'];
  if(array_key_exists('value',$data)) return $data['value'];
  return $default;
}

function _midea39_option($v,$map){
  $s=trim((string)$v);
  if($s==='') return '';
  if(is_numeric($s)){
    $i=intval($s);
    return array_key_exists($i,$map) ? $map[$i] : '';
  }
  $u=strtoupper(str_replace(array(' ','-'),array('_','_'),$s));
  foreach($map as $m){
    if($u===$m) return $m;
  }
  return $u;
}

function _midea39_temp($v){
  $s=str_replace(',','.',trim((string)$v));
  if(!is_numeric($s)) return '';
  $t=floatval($s);
  if($t<16) $t=16;
  if($t>30) $t=30;
  return rtrim(rtrim(number_format($t,1,'.',''),'0'),'.');
}

function _midea39_add_select_command(&$commands,$E,$input,$label,$base,$domain,$name,$queryKey,$value){
  if(isset($E[$input]) && $E[$input]['refresh']==1 && intval($E[$input]['value'])!=0){
    $commands[]=array($label,_midea39_path($base,$domain,$name,'set',$queryKey.'='.rawurlencode($value)));
  }
}

function _midea39_add_switch_command(&$commands,$E,$input,$label,$base,$name,$id){
  if(isset($E[$input]) && $E[$input]['refresh']==1){
    $cache=json_decode((string)logic_getVar($id,101),true);
    if(!is_array($cache)) $cache=array();
    $key=strval($input);
    $state=intval($E[$input]['value'])!=0 ? 1 : 0;
    $had=array_key_exists($key,$cache);
    $old=$had ? intval($cache[$key]) : null;

    if($state==1 || ($had && $old!==$state)){
      $action=$state==1 ? 'turn_on' : 'turn_off';
      $commands[]=array($label.' '.($action==='turn_on'?'AN':'AUS'),_midea39_path($base,'switch',$name,$action));
    }

    $cache[$key]=$state;
    logic_setVar($id,101,json_encode($cache));
  }
}

function _midea39_add_button_command(&$commands,$E,$input,$label,$base,$name){
  if(isset($E[$input]) && $E[$input]['refresh']==1 && intval($E[$input]['value'])!=0){
    $commands[]=array($label,_midea39_path($base,'button',$name,'press'));
  }
}

function _midea39_exec_set_changed($id,$out,$val){
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

if(!($E=logic_getInputsQueued($id))) $E=logic_getInputs($id);
if($E){
  $set=function($o,$v) use($id){ _midea39_exec_set_changed($id,$o,$v); };
  $set(7,date('Y-m-d H:i:s'));
  $set(9,'');

  if(intval($E[5]['value'])!=1) return;
  if(!function_exists('curl_init')){
    $set(1,0); $set(2,'curl fehlt'); $set(3,'fehler: curl fehlt');
    return;
  }

  $base=_midea39_base($E[1]['value']);
  $user=trim((string)$E[2]['value']);
  $pass=(string)$E[3]['value'];
  $timeout=max(1,min(15,intval($E[7]['value'])));
  $sslVerify=intval($E[8]['value'])==1;
  $debug=intval($E[9]['value'])==1;

  if($base===''){
    $set(1,0); $set(2,'ESPHome URL/IP fehlt'); $set(3,'fehler: ESPHome URL/IP fehlt');
    return;
  }

  $commands=array();
  if(isset($E[10]) && $E[10]['refresh']==1 && trim((string)$E[10]['value'])!==''){
    $temp=_midea39_temp($E[10]['value']);
    if($temp!=='') $commands[]=array('Solltemperatur '.$temp,_midea39_path($base,'climate','Klimaanlage','set','target_temperature='.rawurlencode($temp)));
  }

  _midea39_add_select_command($commands,$E,11,'Kuehlen',$base,'climate','Klimaanlage','mode','COOL');
  _midea39_add_select_command($commands,$E,12,'Heizen',$base,'climate','Klimaanlage','mode','HEAT');
  _midea39_add_select_command($commands,$E,13,'Auto',$base,'climate','Klimaanlage','mode','HEAT_COOL');
  _midea39_add_select_command($commands,$E,14,'Entfeuchten',$base,'climate','Klimaanlage','mode','DRY');
  _midea39_add_select_command($commands,$E,15,'Lueften',$base,'climate','Klimaanlage','mode','FAN_ONLY');
  _midea39_add_select_command($commands,$E,16,'Aus',$base,'climate','Klimaanlage','mode','OFF');
  _midea39_add_select_command($commands,$E,17,'Boost',$base,'climate','Klimaanlage','preset','BOOST');
  _midea39_add_select_command($commands,$E,18,'Eco',$base,'climate','Klimaanlage','preset','ECO');
  _midea39_add_select_command($commands,$E,19,'Normal',$base,'climate','Klimaanlage','preset','NONE');
  _midea39_add_select_command($commands,$E,20,'Fan Auto',$base,'climate','Klimaanlage','fan_mode','AUTO');
  _midea39_add_select_command($commands,$E,21,'Fan Low',$base,'climate','Klimaanlage','fan_mode','LOW');
  _midea39_add_select_command($commands,$E,22,'Fan Medium',$base,'climate','Klimaanlage','fan_mode','MEDIUM');
  _midea39_add_select_command($commands,$E,23,'Fan High',$base,'climate','Klimaanlage','fan_mode','HIGH');
  _midea39_add_select_command($commands,$E,24,'Swing Aus',$base,'climate','Klimaanlage','swing_mode','OFF');
  _midea39_add_select_command($commands,$E,25,'Swing Vertikal',$base,'climate','Klimaanlage','swing_mode','VERTICAL');
  _midea39_add_select_command($commands,$E,26,'Swing Horizontal',$base,'climate','Klimaanlage','swing_mode','HORIZONTAL');
  _midea39_add_select_command($commands,$E,27,'Swing Beides',$base,'climate','Klimaanlage','swing_mode','BOTH');
  _midea39_add_switch_command($commands,$E,28,'Fresh',$base,'Midea Fresh',$id);
  _midea39_add_switch_command($commands,$E,29,'Clean',$base,'Midea Clean',$id);
  _midea39_add_button_command($commands,$E,30,'Clean Reset',$base,'Midea Clean Reset');
  _midea39_add_switch_command($commands,$E,31,'Clean Restore',$base,'Midea Clean Restore',$id);
  if(isset($E[32]) && $E[32]['refresh']==1 && trim((string)$E[32]['value'])!==''){
    $minutes=max(15,min(240,intval($E[32]['value'])));
    $commands[]=array('Clean Dauer '.$minutes,_midea39_path($base,'number','Midea Clean Dauer','set','value='.rawurlencode($minutes)));
  }
  _midea39_add_button_command($commands,$E,40,'Power Toggle',$base,'Midea Power Toggle');
  _midea39_add_button_command($commands,$E,41,'Display Toggle',$base,'Midea Display Toggle');
  _midea39_add_button_command($commands,$E,42,'Swing Step',$base,'Midea Swing Step');
  _midea39_add_button_command($commands,$E,43,'Follow Me 24C Test',$base,'Midea Follow Me 24C Test');

  $cmdStatus=array();
  $lastCode=0;
  foreach($commands as $cmd){
    $r=_midea39_curl($cmd[1],'POST',$user,$pass,$timeout,$sslVerify);
    $lastCode=$r['code'];
    $cmdStatus[]=array('cmd'=>$cmd[0],'ok'=>$r['ok']?1:0,'http'=>$r['code'],'err'=>$r['error']);
  }
  if(count($cmdStatus)>0){
    $labels=array();
    foreach($cmdStatus as $c) $labels[]=$c['cmd'].': '.($c['ok']?'ok':'HTTP '.$c['http'].' '.$c['err']);
    $set(8,$cmdStatus[count($cmdStatus)-1]['cmd']);
    $set(9,implode(' | ',$labels));
  }

  $entities=array(
    'climate'=>array('climate','Klimaanlage'),
    'fresh'=>array('switch','Midea Fresh'),
    'clean'=>array('switch','Midea Clean'),
    'clean_restore'=>array('switch','Midea Clean Restore'),
    'clean_duration'=>array('number','Midea Clean Dauer'),
    'clean_state'=>array('text_sensor','Midea Clean State'),
    'clean_remaining'=>array('sensor','Midea Clean Remaining'),
    'last_control_source'=>array('text_sensor','Midea Last Control Source'),
    'wifi_signal'=>array('sensor','Klima WiFi Signal'),
    'uptime'=>array('sensor','Klima Uptime')
  );

  $state=array();
  $errors=array();
  foreach($entities as $key=>$def){
    $g=_midea39_get_entity($base,$def[0],$def[1],$user,$pass,$timeout,$sslVerify,true);
    $lastCode=$g['result']['code'];
    if($g['ok']){
      $state[$key]=$g['data'];
    } else {
      $errors[]=$key.' HTTP '.$g['result']['code'].' '.$g['result']['error'];
    }
  }

  $climateData=isset($state['climate'])?$state['climate']:array();
  $freshData=isset($state['fresh'])?$state['fresh']:array();
  $cleanData=isset($state['clean'])?$state['clean']:array();
  $cleanRestoreData=isset($state['clean_restore'])?$state['clean_restore']:array();
  $cleanDurationData=isset($state['clean_duration'])?$state['clean_duration']:array();
  $cleanStateData=isset($state['clean_state'])?$state['clean_state']:array();
  $cleanRemainingData=isset($state['clean_remaining'])?$state['clean_remaining']:array();
  $lastSourceData=isset($state['last_control_source'])?$state['last_control_source']:array();
  $wifiData=isset($state['wifi_signal'])?$state['wifi_signal']:array();
  $uptimeData=isset($state['uptime'])?$state['uptime']:array();

  $mode=_midea39_field($climateData,'mode');
  $fanMode=_midea39_field($climateData,'fan_mode');
  $preset=_midea39_field($climateData,'preset');
  $swingMode=_midea39_field($climateData,'swing_mode');
  $currentTemp=_midea39_field($climateData,'current_temperature');
  $targetTemp=_midea39_field($climateData,'target_temperature');
  $fresh=_midea39_bool(_midea39_field($freshData,'value',_midea39_state($freshData)));
  $clean=_midea39_bool(_midea39_field($cleanData,'value',_midea39_state($cleanData)));
  $cleanState=_midea39_state($cleanStateData);
  $cleanRemaining=_midea39_field($cleanRemainingData,'value',_midea39_state($cleanRemainingData));
  $cleanRestore=_midea39_bool(_midea39_field($cleanRestoreData,'value',_midea39_state($cleanRestoreData)));
  $cleanDuration=_midea39_field($cleanDurationData,'value',_midea39_state($cleanDurationData));
  $lastSource=_midea39_state($lastSourceData);
  $wifi=_midea39_field($wifiData,'value',_midea39_state($wifiData));
  $uptime=_midea39_field($uptimeData,'value',_midea39_state($uptimeData));
  $modeOn=($mode!=='' && $mode!=='OFF') ? 1 : 0;

  $visu=array(
    'version'=>1,
    'ts'=>time(),
    'ok'=>count($errors)==0 ? 1 : 0,
    'mode'=>$mode,
    'fan_mode'=>$fanMode,
    'preset'=>$preset,
    'swing_mode'=>$swingMode,
    'current_temperature'=>_midea39_num($currentTemp),
    'target_temperature'=>_midea39_num($targetTemp),
    'fresh'=>$fresh,
    'clean'=>$clean,
    'clean_state'=>$cleanState,
    'clean_remaining'=>_midea39_num($cleanRemaining),
    'clean_restore'=>$cleanRestore,
    'clean_duration'=>_midea39_num($cleanDuration),
    'last_control_source'=>$lastSource,
    'wifi_signal'=>_midea39_num($wifi),
    'uptime'=>_midea39_num($uptime)
  );
  if(count($cmdStatus)>0) $visu['commands']=$cmdStatus;

  $json=json_encode($visu,JSON_UNESCAPED_UNICODE);

  $set(4,$lastCode);
  $set(5,$json);
  $set(10,$targetTemp);
  $set(11,$mode==='COOL'?1:0);
  $set(12,$mode==='HEAT'?1:0);
  $set(13,$mode==='HEAT_COOL'?1:0);
  $set(14,$mode==='DRY'?1:0);
  $set(15,$mode==='FAN_ONLY'?1:0);
  $set(16,$mode==='OFF'?1:0);
  $set(17,$preset==='BOOST'?1:0);
  $set(18,$preset==='ECO'?1:0);
  $set(19,($preset==='' || $preset==='NONE')?1:0);
  $set(20,$fanMode==='AUTO'?1:0);
  $set(21,$fanMode==='LOW'?1:0);
  $set(22,$fanMode==='MEDIUM'?1:0);
  $set(23,$fanMode==='HIGH'?1:0);
  $set(24,$swingMode==='OFF'?1:0);
  $set(25,$swingMode==='VERTICAL'?1:0);
  $set(26,$swingMode==='HORIZONTAL'?1:0);
  $set(27,$swingMode==='BOTH'?1:0);
  $set(28,$fresh);
  $set(29,$clean);
  $set(30,$cleanState);
  $set(31,$cleanRestore);
  $set(32,$cleanDuration);
  $set(33,$modeOn);
  $set(34,$mode);
  $set(35,$fanMode);
  $set(36,$preset);
  $set(37,$swingMode);
  $set(38,$currentTemp);
  $set(39,$cleanRemaining);
  $set(40,$lastSource);
  $set(41,$wifi);
  $set(42,$uptime);
  $set(43,$lastCode);

  if(count($errors)==0){
    $set(1,1);
    $set(2,'');
    $set(3,'ok: ESPHome Midea X3 gelesen'.(count($cmdStatus)>0?' + '.count($cmdStatus).' Befehl(e)':''));
  } else {
    $set(1,0);
    $set(2,_midea39_short(implode(' | ',$errors),500));
    $set(3,'fehler: '.count($errors).' Entity-Requests fehlgeschlagen');
  }

  if($debug){
    $dbg=array('base'=>$base,'entities'=>count($entities),'errors'=>$errors,'commands'=>$cmdStatus,'json_bytes'=>strlen($json));
    $set(6,json_encode($dbg,JSON_UNESCAPED_UNICODE));
  } else {
    $set(6,'');
  }
}
?>
###[/EXEC]###
