###[DEF]###
[name = ESPHome Tesla BLE 2.0]
[titel = ESPHome Tesla BLE Status und Steuerung 2.0]
[version = 2.0]

[e#1 = ESPHome URL/IP #init=http://10.0.1.141]
[e#2 = User #init=root]
[e#3 = Passwort]
[e#4 TRIGGER = Lesen/Refresh]
[e#5 = Aktiv 1/0 #init=1]
[e#6 = Zyklus Sekunden #init=30]
[e#7 = HTTP Timeout Sekunden #init=5]
[e#8 = SSL pruefen 1/0 #init=0]
[e#9 = Debug 1/0 #init=0]

[e#10 TRIGGER = Frunk oeffnen]
[e#11 TRIGGER = Ladekabel entriegeln]
[e#12 TRIGGER = Laden starten]
[e#13 TRIGGER = Laden stoppen]
[e#14 TRIGGER = Ladeampere Soll]
[e#15 = Ladeampere bei Aenderung senden 1/0 #init=1]
[e#16 TRIGGER = Ladelimit Prozent]
[e#17 = Ladelimit bei Aenderung senden 1/0 #init=1]
[e#18 TRIGGER = Tueren verriegeln]
[e#19 TRIGGER = Tueren entriegeln]
[e#20 TRIGGER = Force data update]
[e#21 TRIGGER = Charge Port oeffnen]
[e#22 TRIGGER = Charge Port schliessen]

[a#1 = OK 1/0]
[a#2 = Fehlertext]
[a#3 = Status Text]
[a#4 = HTTP Status letzter Request]
[a#5 = Visu JSON]
[a#6 = Debug JSON]
[a#7 = Zeitstempel]
[a#8 = Batterie %]
[a#9 = Reichweite]
[a#10 = Laden Status]
[a#11 = Kabel verbunden 1/0]
[a#12 = Laden aktiv 1/0]
[a#13 = Ladeleistung kW]
[a#14 = Ladestrom A]
[a#15 = Ladeampere Soll A]
[a#16 = Ladelimit %]
[a#17 = Zeit bis voll min]
[a#18 = Energie geladen kWh]
[a#19 = Tueren verriegelt 1/0]
[a#20 = Ladekabel Latch verriegelt 1/0]
[a#21 = Charge Port Door geschlossen 1/0]
[a#22 = Frunk geschlossen 1/0]
[a#23 = Trunk geschlossen 1/0]
[a#24 = Asleep 1/0]
[a#25 = User Present 1/0]
[a#26 = Parking Brake 1/0]
[a#27 = BLE Signal dBm]
[a#28 = WiFi Signal dBm]
[a#29 = Last Command]
[a#30 = Command Status]
[a#31 = Fahrzeug verbunden 1/0]

[v#91 = 0] Pending
[v#100 = ] letzte Ausgaenge JSON
###[/DEF]###

###[HELP]###
Version: 2.0

ESPHome Tesla BLE (19100836)

Zweck:
- Liest wichtige ESPHome-Tesla-BLE-Entities per ESPHome Web API.
- Gibt zentrale Werte einzeln und als kompaktes JSON fuer eine spaetere VSE aus.
- Sendet Steuerbefehle an ESPHome: Frunk oeffnen, Ladekabel entriegeln, Laden Start/Stop, Ladeampere, Ladelimit, Tueren Lock/Unlock, Charge Port Door.
- A31 zeigt den ESPHome-BLE-Verbindungsstatus des Fahrzeugs aus switch/BLE Connection als 1/0.
- A19..A23 sind numerische Statuswerte: 1 = geschlossen/verriegelt, 0 = offen/entriegelt. Unbekannte Rohwerte bleiben leer.

ESPHome-Seite:
- web_server muss aktiv sein.
- Bei gesetzter Authentifizierung E2/E3 belegen, z.B. User root und Passwort.
- Der Baustein nutzt HTTP-REST-Endpunkte wie /sensor/Battery?detail=all und POST /lock/Charge%20Port%20Latch/unlock.

Eingaenge:
- E1: URL oder IP, z.B. http://10.0.1.141 oder 10.0.1.141
- E2/E3: Webserver-User/Passwort, Passwort wird nicht ausgegeben
- E4: manueller Lese-Trigger
- E5: Aktiv
- E6: zyklisches Lesen in Sekunden, 0 = nur Trigger/Konfigaenderung
- E10..E13 und E18..E22: Steuerbefehle, nur bei Trigger und Wert != 0
- E14: Ladeampere Soll. Bei Aenderung wird direkt gesendet, wenn E15=1 ist.
- E15: Senden von E14 bei Aenderung freigeben.
- E16: Ladelimit Prozent. Bei Aenderung wird direkt gesendet, wenn E17=1 ist.
- E17: Senden von E16 bei Aenderung freigeben.

Hinweise:
- HTTP laeuft im EXEC-Teil.
- Ausgaenge werden nur bei Wertwechsel geschrieben.
- Steuerbefehle werden vor dem Lesen ausgefuehrt; danach werden die Statuswerte neu gelesen.
###[/HELP]###

###[LBS]###
<?php
function _tesla36_lbs_set_changed($id,$out,$val){
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
  for($i=1;$i<=22;$i++){
    if(isset($E[$i]) && $E[$i]['refresh']==1){ $changed=true; break; }
  }

  $pending=intval(logic_getVar($id,91))==1;
  $timer=(intval(logic_getState($id))==1);
  if(!$changed && !$pending && !$timer) return;

  $set=function($o,$v) use($id){ _tesla36_lbs_set_changed($id,$o,$v); };

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
  if($cycle>0) logic_setState($id,1,$cycle*1000);
  else logic_setState($id,0);
}
?>
###[/LBS]###

###[EXEC]###
<?php
require(dirname(__FILE__)."/../../../../main/include/php/incl_lbsexec.php");
set_time_limit(25);
sql_connect();

function _tesla36_short($s,$len=300){
  $s=str_replace(array("\r","\n","\t"),' ',(string)$s);
  return strlen($s)>$len ? substr($s,0,$len).'...' : $s;
}

function _tesla36_bool($v){
  if(is_bool($v)) return $v ? 1 : 0;
  $s=strtolower(trim((string)$v));
  return ($s==='1' || $s==='on' || $s==='true' || $s==='yes') ? 1 : 0;
}

function _tesla36_closed_bool($v,$default=''){
  if(is_bool($v)) return $v ? 1 : 0;
  $s=strtolower(trim((string)$v));
  if($s==='') return $default;
  if(in_array($s,array('1','on','true','yes','closed','close','locked','lock'),true)) return 1;
  if(in_array($s,array('0','off','false','no','open','opening','opened','unlocked','unlock'),true)) return 0;
  return $default;
}

function _tesla36_num($v,$default=''){
  if(is_numeric($v)) return $v+0;
  return $default;
}

function _tesla36_base($host){
  $host=trim((string)$host);
  if($host!=='' && strpos($host,'http://')!==0 && strpos($host,'https://')!==0) $host='http://'.$host;
  return rtrim($host,'/');
}

function _tesla36_path($base,$domain,$name,$action='',$query=''){
  $url=$base.'/'.rawurlencode($domain).'/'.rawurlencode($name);
  if($action!=='') $url.='/'.rawurlencode($action);
  if($query!=='') $url.='?'.$query;
  return $url;
}

function _tesla36_curl($url,$method,$user,$pass,$timeout,$sslVerify,$body=null){
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

function _tesla36_get_entity($base,$domain,$name,$user,$pass,$timeout,$sslVerify){
  $url=_tesla36_path($base,$domain,$name,'','detail=all');
  $r=_tesla36_curl($url,'GET',$user,$pass,$timeout,$sslVerify);
  if(!$r['ok']) return array('ok'=>false,'result'=>$r,'data'=>null);
  $data=json_decode((string)$r['body'],true);
  if(!is_array($data)) return array('ok'=>false,'result'=>$r,'data'=>null,'json_error'=>json_last_error_msg());
  return array('ok'=>true,'result'=>$r,'data'=>$data);
}

function _tesla36_val($state,$key,$default=''){
  if(isset($state[$key]) && is_array($state[$key]) && array_key_exists('value',$state[$key])) return $state[$key]['value'];
  if(isset($state[$key]) && is_array($state[$key]) && array_key_exists('state',$state[$key])) return $state[$key]['state'];
  return $default;
}

function _tesla36_state($state,$key,$default=''){
  if(isset($state[$key]) && is_array($state[$key]) && array_key_exists('state',$state[$key])) return $state[$key]['state'];
  if(isset($state[$key]) && is_array($state[$key]) && array_key_exists('value',$state[$key])) return $state[$key]['value'];
  return $default;
}

function _tesla36_exec_set_changed($id,$out,$val){
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
  $set=function($o,$v) use($id){ _tesla36_exec_set_changed($id,$o,$v); };
  $set(7,date('Y-m-d H:i:s'));
  $set(30,'');

  if(intval($E[5]['value'])!=1) return;
  if(!function_exists('curl_init')){
    $set(1,0); $set(2,'curl fehlt'); $set(3,'fehler: curl fehlt');
    return;
  }

  $base=_tesla36_base($E[1]['value']);
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
  if(isset($E[10]) && $E[10]['refresh']==1 && intval($E[10]['value'])!=0) $commands[]=array('Frunk oeffnen',_tesla36_path($base,'cover','Frunk','open'));
  if(isset($E[11]) && $E[11]['refresh']==1 && intval($E[11]['value'])!=0) $commands[]=array('Ladekabel entriegeln',_tesla36_path($base,'lock','Charge Port Latch','unlock'));
  if(isset($E[12]) && $E[12]['refresh']==1 && intval($E[12]['value'])!=0) $commands[]=array('Laden starten',_tesla36_path($base,'switch','Charger','turn_on'));
  if(isset($E[13]) && $E[13]['refresh']==1 && intval($E[13]['value'])!=0) $commands[]=array('Laden stoppen',_tesla36_path($base,'switch','Charger','turn_off'));
  if(isset($E[14]) && $E[14]['refresh']==1 && intval($E[15]['value'])==1 && trim((string)$E[14]['value'])!==''){
    $amps=max(0,min(80,intval($E[14]['value'])));
    $commands[]=array('Ladeampere setzen '.$amps,_tesla36_path($base,'number','Charging Amps','set','value='.rawurlencode($amps)));
  }
  if(isset($E[16]) && $E[16]['refresh']==1 && intval($E[17]['value'])==1 && trim((string)$E[16]['value'])!==''){
    $limit=max(50,min(100,intval($E[16]['value'])));
    $commands[]=array('Ladelimit setzen '.$limit,_tesla36_path($base,'number','Charging Limit','set','value='.rawurlencode($limit)));
  }
  if(isset($E[18]) && $E[18]['refresh']==1 && intval($E[18]['value'])!=0) $commands[]=array('Tueren verriegeln',_tesla36_path($base,'lock','Doors','lock'));
  if(isset($E[19]) && $E[19]['refresh']==1 && intval($E[19]['value'])!=0) $commands[]=array('Tueren entriegeln',_tesla36_path($base,'lock','Doors','unlock'));
  if(isset($E[20]) && $E[20]['refresh']==1 && intval($E[20]['value'])!=0) $commands[]=array('Force data update',_tesla36_path($base,'button','Force data update','press'));
  if(isset($E[21]) && $E[21]['refresh']==1 && intval($E[21]['value'])!=0) $commands[]=array('Charge Port oeffnen',_tesla36_path($base,'cover','Charge Port Door','open'));
  if(isset($E[22]) && $E[22]['refresh']==1 && intval($E[22]['value'])!=0) $commands[]=array('Charge Port schliessen',_tesla36_path($base,'cover','Charge Port Door','close'));

  $cmdStatus=array();
  $lastCode=0;
  foreach($commands as $cmd){
    $r=_tesla36_curl($cmd[1],'POST',$user,$pass,$timeout,$sslVerify);
    $lastCode=$r['code'];
    $cmdStatus[]=array('cmd'=>$cmd[0],'ok'=>$r['ok']?1:0,'http'=>$r['code'],'err'=>$r['error']);
  }
  if(count($cmdStatus)>0){
    $labels=array();
    foreach($cmdStatus as $c) $labels[]=$c['cmd'].': '.($c['ok']?'ok':'HTTP '.$c['http'].' '.$c['err']);
    $set(30,implode(' | ',$labels));
  }

  $entities=array(
    'status'=>array('binary_sensor','Status'),
    'asleep'=>array('binary_sensor','Asleep'),
    'user_present'=>array('binary_sensor','User Present'),
    'charger_connected'=>array('binary_sensor','Charger'),
    'parking_brake'=>array('binary_sensor','Parking Brake'),
    'charge_port_door'=>array('cover','Charge Port Door'),
    'frunk'=>array('cover','Frunk'),
    'trunk'=>array('cover','Trunk'),
    'windows'=>array('cover','Windows'),
    'battery'=>array('sensor','Battery'),
    'range'=>array('sensor','Range'),
    'charger_power'=>array('sensor','Charger Power'),
    'charger_voltage'=>array('sensor','Charger Voltage'),
    'charger_current'=>array('sensor','Charger Current'),
    'charger_max'=>array('sensor','Charger Max'),
    'car_max_acceptable'=>array('sensor','Car Max Acceptable'),
    'charger_phases'=>array('sensor','Charger Phases'),
    'charging_rate'=>array('sensor','Charging Rate'),
    'energy_added'=>array('sensor','Energy Added'),
    'time_to_full'=>array('sensor','Time to Full'),
    'outside_temperature'=>array('sensor','Outside Temperature'),
    'ble_signal'=>array('sensor','BLE Signal'),
    'wifi_signal'=>array('sensor','WiFi Signal'),
    'charger_switch'=>array('switch','Charger'),
    'sentry_mode'=>array('switch','Sentry Mode'),
    'ble_connection'=>array('switch','BLE Connection'),
    'charging'=>array('text_sensor','Charging'),
    'charge_limit_reason'=>array('text_sensor','Charge Limit Reason'),
    'last_command'=>array('text_sensor','Last Command'),
    'charging_amps'=>array('number','Charging Amps'),
    'charging_limit'=>array('number','Charging Limit'),
    'doors'=>array('lock','Doors'),
    'charge_port_latch'=>array('lock','Charge Port Latch')
  );

  $state=array();
  $errors=array();
  foreach($entities as $key=>$def){
    $g=_tesla36_get_entity($base,$def[0],$def[1],$user,$pass,$timeout,$sslVerify);
    $lastCode=$g['result']['code'];
    if($g['ok']){
      $state[$key]=$g['data'];
    } else {
      $errors[]=$key.' HTTP '.$g['result']['code'].' '.$g['result']['error'];
    }
  }

  $battery=_tesla36_val($state,'battery');
  $range=_tesla36_val($state,'range');
  $charging=_tesla36_state($state,'charging');
  $chargerConnected=_tesla36_bool(_tesla36_val($state,'charger_connected'));
  $chargerSwitch=_tesla36_bool(_tesla36_val($state,'charger_switch'));
  $chargerPower=_tesla36_val($state,'charger_power');
  $chargerCurrent=_tesla36_val($state,'charger_current');
  $chargingAmps=_tesla36_val($state,'charging_amps');
  $chargingLimit=_tesla36_val($state,'charging_limit');
  $timeToFull=_tesla36_val($state,'time_to_full');
  $energyAdded=_tesla36_val($state,'energy_added');
  $doorsRaw=_tesla36_state($state,'doors');
  $chargeLatchRaw=_tesla36_state($state,'charge_port_latch');
  $chargeDoorRaw=_tesla36_state($state,'charge_port_door');
  $frunkRaw=_tesla36_state($state,'frunk');
  $trunkRaw=_tesla36_state($state,'trunk');
  $doors=_tesla36_closed_bool($doorsRaw);
  $chargeLatch=_tesla36_closed_bool($chargeLatchRaw);
  $chargeDoor=_tesla36_closed_bool($chargeDoorRaw);
  $frunk=_tesla36_closed_bool($frunkRaw);
  $trunk=_tesla36_closed_bool($trunkRaw);
  $asleep=_tesla36_bool(_tesla36_val($state,'asleep'));
  $userPresent=_tesla36_bool(_tesla36_val($state,'user_present'));
  $parkingBrake=_tesla36_bool(_tesla36_val($state,'parking_brake'));
  $vehicleConnected=_tesla36_bool(_tesla36_val($state,'ble_connection'));
  $ble=_tesla36_val($state,'ble_signal');
  $wifi=_tesla36_val($state,'wifi_signal');
  $lastCommand=_tesla36_state($state,'last_command');

  $visu=array(
    'version'=>1,
    'ts'=>time(),
    'ok'=>count($errors)==0 ? 1 : 0,
    'battery'=>_tesla36_num($battery),
    'range'=>_tesla36_num($range),
    'charging'=>$charging,
    'charger_connected'=>$chargerConnected,
    'charger_on'=>$chargerSwitch,
    'charger_power'=>_tesla36_num($chargerPower),
    'charger_current'=>_tesla36_num($chargerCurrent),
    'charging_amps'=>_tesla36_num($chargingAmps),
    'charging_limit'=>_tesla36_num($chargingLimit),
    'time_to_full'=>_tesla36_num($timeToFull),
    'energy_added'=>_tesla36_num($energyAdded),
    'doors'=>$doors,
    'charge_port_latch'=>$chargeLatch,
    'charge_port_door'=>$chargeDoor,
    'frunk'=>$frunk,
    'trunk'=>$trunk,
    'raw'=>array(
      'doors'=>$doorsRaw,
      'charge_port_latch'=>$chargeLatchRaw,
      'charge_port_door'=>$chargeDoorRaw,
      'frunk'=>$frunkRaw,
      'trunk'=>$trunkRaw
    ),
    'asleep'=>$asleep,
    'user_present'=>$userPresent,
    'parking_brake'=>$parkingBrake,
    'vehicle_connected'=>$vehicleConnected,
    'ble_signal'=>_tesla36_num($ble),
    'wifi_signal'=>_tesla36_num($wifi),
    'last_command'=>$lastCommand
  );
  if(count($cmdStatus)>0) $visu['commands']=$cmdStatus;

  $json=json_encode($visu,JSON_UNESCAPED_UNICODE);

  $set(4,$lastCode);
  $set(5,$json);
  $set(8,$battery);
  $set(9,$range);
  $set(10,$charging);
  $set(11,$chargerConnected);
  $set(12,$chargerSwitch);
  $set(13,$chargerPower);
  $set(14,$chargerCurrent);
  $set(15,$chargingAmps);
  $set(16,$chargingLimit);
  $set(17,$timeToFull);
  $set(18,$energyAdded);
  $set(19,$doors);
  $set(20,$chargeLatch);
  $set(21,$chargeDoor);
  $set(22,$frunk);
  $set(23,$trunk);
  $set(24,$asleep);
  $set(25,$userPresent);
  $set(26,$parkingBrake);
  $set(27,$ble);
  $set(28,$wifi);
  $set(29,$lastCommand);
  $set(31,$vehicleConnected);

  if(count($errors)==0){
    $set(1,1);
    $set(2,'');
    $set(3,'ok: ESPHome Tesla BLE gelesen'.(count($cmdStatus)>0?' + '.count($cmdStatus).' Befehl(e)':''));
  } else {
    $set(1,0);
    $set(2,_tesla36_short(implode(' | ',$errors),500));
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
