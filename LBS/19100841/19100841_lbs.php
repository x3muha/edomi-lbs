###[DEF]###
[name = Hoymiles DTUBI 1.0]
[titel = Hoymiles DTUBI lokale TCP-Abfrage 1.0]
[version = 1.0]

[e#1 TRIGGER = Lesen/Refresh]
[e#2 = Aktiv 1/0 #init=1]
[e#3 = DTU IP-Adresse #init=10.0.0.100]
[e#4 = Anzahl DC-Strings #init=2]
[e#5 = Timeout Sekunden #init=5]
[e#6 = Zyklus Sekunden #init=0]
[e#7 = Debug 1/0 #init=0]

[a#1 = AC Leistung W]
[a#2 = AC Spannung V]
[a#3 = AC Strom A]
[a#4 = AC Frequenz Hz]
[a#5 = Tagesertrag Wh]
[a#6 = Gesamtertrag kWh]
[a#7 = Temperatur C]
[a#8 = Status Text]
[a#9 = DC Leistung gesamt W]
[a#10 = DC String 1 Spannung V]
[a#11 = DC String 1 Strom A]
[a#12 = DC String 1 Leistung W]
[a#13 = DC String 2 Spannung V]
[a#14 = DC String 2 Strom A]
[a#15 = DC String 2 Leistung W]
[a#16 = DC String 3 Spannung V]
[a#17 = DC String 3 Strom A]
[a#18 = DC String 3 Leistung W]
[a#19 = DC String 4 Spannung V]
[a#20 = DC String 4 Strom A]
[a#21 = DC String 4 Leistung W]
[a#22 = JSON Werte]
[a#23 = Erreichbar 1/0]
[a#24 = Produziert 1/0]
[a#25 = Wirkungsgrad Prozent]

[v#91 = 0] Pending
[v#100 = ] letzte Ausgaenge JSON
###[/DEF]###

###[HELP]###
Version: 1.0

Hoymiles DTUBI (19100841)

Zweck:
- Liest Hoymiles HMS/HMT Wechselrichter mit integrierter WLAN-DTU lokal ueber TCP/Protobuf.
- Nutzt die Python-Bibliothek hoymiles-wifi auf dem EDOMI-Server.

Voraussetzungen auf dem EDOMI-Server:
- Python 3, bevorzugt python3.11 oder python3.
- Python-Paket hoymiles-wifi, z.B. per pip install hoymiles-wifi.
- Netzwerkzugriff vom EDOMI-Server zur DTU.

Eingaenge:
- E1: manueller Lese-Trigger.
- E2: Aktiv 1/0.
- E3: IP-Adresse der DTU, z.B. 10.0.0.66.
- E4: Anzahl DC-Strings, 1..4.
- E5: Timeout in Sekunden.
- E6: Zyklus in Sekunden. 0 = nur per Trigger, >0 = zyklisch lesen.
- E7: Debug 1/0, schreibt Details ins Custom-Log.

Ausgaenge:
- A1..A9: AC-/Ertrags-/Temperatur-/Statuswerte und DC-Gesamtleistung.
- A10..A21: DC-Strings 1..4 mit Spannung, Strom und Leistung.
- A22: kompaktes JSON mit allen gelesenen Werten.
- A23: Erreichbar 1/0.
- A24: Produziert 1/0.
- A25: Wirkungsgrad in Prozent.

Hinweise:
- Die DTUBI erlaubt normalerweise nur eine lokale TCP-Verbindung gleichzeitig.
- Bei zu kurzem Zyklus kann die Hoymiles-App oder eine andere lokale Abfrage gestoert werden.
- Ein Zyklus von 60 Sekunden oder groesser ist fuer produktive Anlagen sinnvoll.
###[/HELP]###

###[LBS]###
<?php
function _hm41_lbs_set_changed($id,$out,$val){
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

  $trigger=(isset($E[1]) && $E[1]['refresh']==1);
  $timer=(intval(logic_getState($id))==1);
  $pending=(intval(logic_getVar($id,91))==1);
  if(!$trigger && !$timer && !$pending) return;

  $set=function($o,$v) use($id){ _hm41_lbs_set_changed($id,$o,$v); };

  if(intval($E[2]['value'])!=1){
    logic_setState($id,0);
    logic_setVar($id,91,0);
    $set(23,0);
    $set(8,'deaktiviert');
    return;
  }

  logic_setInputsQueued($id,$E);
  if(logic_getStateExec($id)==0){
    logic_setVar($id,91,0);
    $set(8,'queued');
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
set_time_limit(40);
sql_connect();

function _hm41_short($s,$len=300){
  $s=str_replace(array("\r","\n","\t"),' ',(string)$s);
  return strlen($s)>$len ? substr($s,0,$len).'...' : $s;
}

function _hm41_log($enabled,$id,$text){
  if($enabled) writeToCustomLog('LBS_HoymilesDTUBI-'.$id,'-',$text);
}

function _hm41_set_changed($id,$out,$val){
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

function _hm41_find_python(){
  $candidates=array('/usr/bin/python3.11','/usr/local/bin/python3.11','/usr/bin/python3','/usr/local/bin/python3');
  foreach($candidates as $p){
    if(is_file($p) && is_executable($p)) return $p;
  }
  $out=array();
  $ret=1;
  exec('command -v python3.11 2>/dev/null',$out,$ret);
  if($ret===0 && isset($out[0]) && trim($out[0])!=='') return trim($out[0]);
  $out=array();
  exec('command -v python3 2>/dev/null',$out,$ret);
  if($ret===0 && isset($out[0]) && trim($out[0])!=='') return trim($out[0]);
  return '';
}

function _hm41_helper_code(){
  return <<<'PY'
import asyncio
import json
import sys

def finish(ok, error="", data=None):
    base = {
        "ok": bool(ok),
        "error": str(error or ""),
        "serial": "",
        "name": "",
        "reachable": False,
        "producing": False,
        "ac_power": 0.0,
        "ac_voltage": 0.0,
        "ac_current": 0.0,
        "ac_freq": 0.0,
        "yield_day": 0.0,
        "yield_total": 0.0,
        "temperature": 0.0,
        "dc_strings": [],
    }
    if isinstance(data, dict):
        base.update(data)
    print(json.dumps(base, ensure_ascii=False, separators=(",", ":")))
    sys.exit(0 if ok else 1)

def number(value, scale=1.0, digits=1):
    try:
        return round(float(value) / scale, digits)
    except Exception:
        return 0.0

if len(sys.argv) < 2:
    finish(False, "Aufruf: helper.py <ip> [timeout] [strings]")

host = sys.argv[1]
try:
    timeout = max(1, int(sys.argv[2])) if len(sys.argv) > 2 else 5
except Exception:
    timeout = 5
try:
    string_count = max(1, min(4, int(sys.argv[3]))) if len(sys.argv) > 3 else 2
except Exception:
    string_count = 2

try:
    from hoymiles_wifi.dtu import DTU
except Exception as exc:
    finish(False, "Python-Paket hoymiles-wifi fehlt oder ist nicht ladbar: %s" % exc)

def parse_new(resp):
    data = {
        "reachable": True,
        "serial": str(getattr(resp, "device_serial_number", "") or ""),
        "dc_strings": [],
    }
    sgs_items = list(getattr(resp, "sgs_data", []) or [])
    if sgs_items:
        sgs = sgs_items[0]
        data["ac_power"] = number(getattr(sgs, "active_power", 0), 10.0, 1)
        data["ac_voltage"] = number(getattr(sgs, "voltage", 0), 10.0, 1)
        data["ac_current"] = number(getattr(sgs, "current", 0), 100.0, 2)
        data["ac_freq"] = number(getattr(sgs, "frequency", 0), 100.0, 2)
        data["temperature"] = number(getattr(sgs, "temperature", 0), 10.0, 1)
        data["producing"] = data["ac_power"] > 0
    data["yield_day"] = number(getattr(resp, "dtu_daily_energy", 0), 10.0, 0)
    total = 0.0
    daily = 0.0
    pv_items = list(getattr(resp, "pv_data", []) or [])
    for index in range(string_count):
        if index < len(pv_items):
            pv = pv_items[index]
            item = {
                "nr": index + 1,
                "voltage": number(getattr(pv, "voltage", 0), 10.0, 1),
                "current": number(getattr(pv, "current", 0), 100.0, 2),
                "power": number(getattr(pv, "power", 0), 10.0, 1),
            }
            daily += number(getattr(pv, "energy_daily", 0), 10.0, 0)
            total += number(getattr(pv, "energy_total", 0), 1000.0, 3)
        else:
            item = {"nr": index + 1, "voltage": 0.0, "current": 0.0, "power": 0.0}
        data["dc_strings"].append(item)
    if data.get("yield_day", 0) == 0 and daily > 0:
        data["yield_day"] = round(daily, 0)
    if total > 0:
        data["yield_total"] = round(total, 3)
    return data

def parse_old(resp):
    data = {
        "reachable": True,
        "serial": str(getattr(resp, "dtu_sn", "") or ""),
        "dc_strings": [],
    }
    pv_items = list(getattr(resp, "pv_data", []) or [])
    for index in range(string_count):
        if index < len(pv_items):
            pv = pv_items[index]
            if index == 0:
                data["ac_power"] = number(getattr(pv, "grid_p", 0), 10.0, 1)
                data["ac_voltage"] = number(getattr(pv, "grid_vol", 0), 10.0, 1)
                data["ac_current"] = number(getattr(pv, "grid_i", 0), 100.0, 2)
                data["ac_freq"] = number(getattr(pv, "grid_freq", 0), 100.0, 2)
                data["temperature"] = number(getattr(pv, "pv_temp", 0), 10.0, 1)
                data["yield_day"] = number(getattr(pv, "pv_energy", 0), 10.0, 0)
                data["yield_total"] = number(getattr(pv, "pv_energy_total", 0), 1000.0, 3)
                data["producing"] = data["ac_power"] > 0
            item = {
                "nr": index + 1,
                "voltage": number(getattr(pv, "pv_vol", 0), 10.0, 1),
                "current": number(getattr(pv, "pv_cur", 0), 100.0, 2),
                "power": number(getattr(pv, "pv_power", 0), 10.0, 1),
            }
        else:
            item = {"nr": index + 1, "voltage": 0.0, "current": 0.0, "power": 0.0}
        data["dc_strings"].append(item)
    return data

async def read():
    dtu = DTU(host=host, timeout=timeout)
    errors = []
    try:
        new_data = await dtu.async_get_real_data_new()
        if new_data is not None and new_data.ByteSize() > 0:
            return parse_new(new_data)
    except Exception as exc:
        errors.append("new: %s" % exc)
    try:
        old_data = await dtu.async_get_real_data()
        if old_data is not None and old_data.ByteSize() > 0:
            return parse_old(old_data)
    except Exception as exc:
        errors.append("old: %s" % exc)
    raise RuntimeError("; ".join(errors) if errors else "keine Daten")

try:
    finish(True, "", asyncio.run(read()))
except Exception as exc:
    finish(False, "DTU nicht lesbar: %s" % exc)
PY;
}

function _hm41_prepare_helper(){
  $path=sys_get_temp_dir().'/edomi_hoymiles_19100841.py';
  $code=_hm41_helper_code();
  if(!is_file($path) || sha1((string)file_get_contents($path))!==sha1($code)){
    if(file_put_contents($path,$code)===false) return '';
    @chmod($path,0755);
  }
  return $path;
}

$E=logic_getInputsQueued($id);
if(!$E) $E=logic_getInputs($id);
if(!$E){
  return;
}

$debug=(intval($E[7]['value'])==1);
$dtuIp=trim((string)$E[3]['value']);
$strings=max(1,min(4,intval($E[4]['value'])));
$timeout=max(1,min(30,intval($E[5]['value'])));
set_time_limit($timeout+20);

if($dtuIp===''){
  _hm41_set_changed($id,8,'Fehler: keine IP');
  _hm41_set_changed($id,23,0);
  return;
}

$python=_hm41_find_python();
if($python===''){
  _hm41_set_changed($id,8,'Fehler: python3 fehlt');
  _hm41_set_changed($id,23,0);
  return;
}

$helper=_hm41_prepare_helper();
if($helper===''){
  _hm41_set_changed($id,8,'Fehler: interne Laufzeitdatei nicht schreibbar');
  _hm41_set_changed($id,23,0);
  return;
}

$cmd=escapeshellarg($python).' '.escapeshellarg($helper).' '.escapeshellarg($dtuIp).' '.intval($timeout).' '.intval($strings).' 2>/dev/null';
_hm41_log($debug,$id,'Start '.$dtuIp.' Strings='.$strings.' Timeout='.$timeout.' Python='.$python);

$lines=array();
$ret=0;
exec($cmd,$lines,$ret);
$raw=implode('', $lines);
_hm41_log($debug,$id,'Rueckgabe Code='.$ret.' '.substr($raw,0,240));

$j=json_decode($raw,true);
if(!is_array($j)){
  _hm41_set_changed($id,8,'Fehler: Python-Ausgabe ungueltig');
  _hm41_set_changed($id,23,0);
  return;
}

$ok=!empty($j['ok']);
$err=isset($j['error'])?(string)$j['error']:'';
$reachable=!empty($j['reachable']);
$producing=!empty($j['producing']);
$serial=isset($j['serial'])?(string)$j['serial']:'';
$name=isset($j['name'])?(string)$j['name']:'';
$acPower=isset($j['ac_power'])?round(floatval($j['ac_power']),1):0.0;
$acVolt=isset($j['ac_voltage'])?round(floatval($j['ac_voltage']),1):0.0;
$acCurr=isset($j['ac_current'])?round(floatval($j['ac_current']),2):0.0;
$acFreq=isset($j['ac_freq'])?round(floatval($j['ac_freq']),2):0.0;
$yieldDay=isset($j['yield_day'])?round(floatval($j['yield_day']),0):0.0;
$yieldTotal=isset($j['yield_total'])?round(floatval($j['yield_total']),3):0.0;
$temperature=isset($j['temperature'])?round(floatval($j['temperature']),1):0.0;
$dcStrings=(isset($j['dc_strings']) && is_array($j['dc_strings']))?$j['dc_strings']:array();

if(!$ok && $err!=='') $status='Fehler: '._hm41_short($err,120);
else if(!$reachable) $status='nicht erreichbar';
else if($producing) $status='produziert';
else $status='online (kein Ertrag)';

$stringsOut=array();
$dcTotal=0.0;
for($i=0;$i<4;$i++){
  $src=isset($dcStrings[$i]) && is_array($dcStrings[$i]) ? $dcStrings[$i] : array();
  $entry=array(
    'nr'=>$i+1,
    'voltage'=>isset($src['voltage'])?round(floatval($src['voltage']),1):0.0,
    'current'=>isset($src['current'])?round(floatval($src['current']),2):0.0,
    'power'=>isset($src['power'])?round(floatval($src['power']),1):0.0,
  );
  if($i>=$strings){
    $entry['voltage']=0.0;
    $entry['current']=0.0;
    $entry['power']=0.0;
  }
  $dcTotal+=$entry['power'];
  $stringsOut[]=$entry;
}
$dcTotal=round($dcTotal,1);
$efficiency=($dcTotal>0)?round(($acPower/$dcTotal)*100,1):0.0;

$json=json_encode(array(
  'serial'=>$serial,
  'name'=>$name,
  'status'=>$status,
  'reachable'=>$reachable?1:0,
  'producing'=>$producing?1:0,
  'ac'=>array('power'=>$acPower,'voltage'=>$acVolt,'current'=>$acCurr,'frequency'=>$acFreq),
  'dc_total'=>$dcTotal,
  'efficiency'=>$efficiency,
  'yield_day'=>$yieldDay,
  'yield_total'=>$yieldTotal,
  'temperature'=>$temperature,
  'strings'=>array_slice($stringsOut,0,$strings),
  'timestamp'=>time(),
),JSON_UNESCAPED_SLASHES);
if($json===false) $json='{}';

_hm41_set_changed($id,1,$acPower);
_hm41_set_changed($id,2,$acVolt);
_hm41_set_changed($id,3,$acCurr);
_hm41_set_changed($id,4,$acFreq);
_hm41_set_changed($id,5,$yieldDay);
_hm41_set_changed($id,6,$yieldTotal);
_hm41_set_changed($id,7,$temperature);
_hm41_set_changed($id,8,$status);
_hm41_set_changed($id,9,$dcTotal);

for($i=0;$i<4;$i++){
  $base=10+($i*3);
  _hm41_set_changed($id,$base,$stringsOut[$i]['voltage']);
  _hm41_set_changed($id,$base+1,$stringsOut[$i]['current']);
  _hm41_set_changed($id,$base+2,$stringsOut[$i]['power']);
}

_hm41_set_changed($id,22,$json);
_hm41_set_changed($id,23,$reachable?1:0);
_hm41_set_changed($id,24,$producing?1:0);
_hm41_set_changed($id,25,$efficiency);

_hm41_log($debug,$id,'Status='.$status.' AC='.$acPower.'W DC='.$dcTotal.'W Eta='.$efficiency.'%');
?>
###[/EXEC]###
