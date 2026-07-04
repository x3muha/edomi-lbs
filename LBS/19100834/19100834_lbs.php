###[DEF]###
[name = FriWa Wh Verteiler 1.0]
[titel = FriWa Zählerstand auf Zirkulation und 4 Kanäle verteilen]
[version = 1.0]

[e#1 TRIGGER = FriWa Zaehlerstand Wh]
[e#2 = Nachlauf bis letzter Wh-Wert s #init=30]
[e#3 TRIGGER = Reset alle Zaehler]
[e#4 TRIGGER = Reset Zaehlerstands-Referenz]
[e#5 TRIGGER = Reset Restmenge]

[e#10 TRIGGER = Zirkulation Eingang]
[e#11 = Zirkulation Startwert #init=1]
[e#12 = Zirkulation Stopwert #init=0]
[e#13 = Zirkulation live ausgeben 1/0 #init=1]
[e#14 TRIGGER = Reset Zirkulation]

[e#19 = Name]
[e#20 TRIGGER = Kanal 1 Eingang]
[e#21 = Kanal 1 Startwert #init=1]
[e#22 = Kanal 1 Stopwert #init=0]
[e#23 = Kanal 1 live ausgeben 1/0 #init=1]
[e#24 TRIGGER = Reset Kanal 1]

[e#29 = Name]
[e#30 TRIGGER = Kanal 2 Eingang]
[e#31 = Kanal 2 Startwert #init=1]
[e#32 = Kanal 2 Stopwert #init=0]
[e#33 = Kanal 2 live ausgeben 1/0 #init=1]
[e#34 TRIGGER = Reset Kanal 2]

[e#39 = Name]
[e#40 TRIGGER = Kanal 3 Eingang]
[e#41 = Kanal 3 Startwert #init=1]
[e#42 = Kanal 3 Stopwert #init=0]
[e#43 = Kanal 3 live ausgeben 1/0 #init=1]
[e#44 TRIGGER = Reset Kanal 3]

[e#49 = Name]
[e#50 TRIGGER = Kanal 4 Eingang]
[e#51 = Kanal 4 Startwert #init=1]
[e#52 = Kanal 4 Stopwert #init=0]
[e#53 = Kanal 4 live ausgeben 1/0 #init=1]
[e#54 TRIGGER = Reset Kanal 4]

[a#1 = Gesamt gezaehlt Wh]
[a#2 = Letztes Delta Wh]
[a#3 = Ruecksprung erkannt Anzahl]
[a#4 = Aktiver Zaehler]
[a#5 = Status]
[a#6 = Debug JSON]

[a#10 = Zirkulation kumuliert Wh]
[a#11 = Zirkulation letzter Start-Stop Wh]
[a#20 = Kanal 1 kumuliert Wh]
[a#21 = Kanal 1 letzter Start-Stop Wh]
[a#30 = Kanal 2 kumuliert Wh]
[a#31 = Kanal 2 letzter Start-Stop Wh]
[a#40 = Kanal 3 kumuliert Wh]
[a#41 = Kanal 3 letzter Start-Stop Wh]
[a#50 = Kanal 4 kumuliert Wh]
[a#51 = Kanal 4 letzter Start-Stop Wh]
[a#60 = Restmenge kumuliert Wh]

[v#1 REMANENT = 0] Zaehlerstand initialisiert
[v#2 REMANENT = 0] Letzter Zaehlerstand Wh
[v#3 REMANENT = 0] Start-Sequenz
[v#4 REMANENT = 0] Aktiver Zaehler
[v#5 REMANENT = 0] Nachlauf-Zaehler
[v#6 REMANENT = 0] Nachlauf bis Unixzeit
[v#7 REMANENT = 0] Gesamt gezaehltes Delta Wh
[v#8 REMANENT = 0] Ruecksprung Anzahl
[v#9 = ] Cache Status
[v#10 = ] Cache Debug
[v#11 = ] Cache aktiver Zaehler
[v#12 = ] Cache letztes Delta
[v#13 = ] Cache Gesamt Delta
[v#14 = ] Cache Ruecksprung Anzahl

[v#20 REMANENT = 0] Zirkulation aktiv
[v#21 REMANENT = 0] Zirkulation Start-Sequenz
[v#22 REMANENT = 0] Zirkulation kumuliert Wh
[v#23 REMANENT = 0] Zirkulation letzter Start-Stop Wh
[v#24 REMANENT = 0] Zirkulation aktuelle Phase Wh
[v#25 = ] Zirkulation Cache kumuliert
[v#26 = ] Zirkulation Cache letzter Start-Stop

[v#30 REMANENT = 0] Kanal 1 aktiv
[v#31 REMANENT = 0] Kanal 1 Start-Sequenz
[v#32 REMANENT = 0] Kanal 1 kumuliert Wh
[v#33 REMANENT = 0] Kanal 1 letzter Start-Stop Wh
[v#34 REMANENT = 0] Kanal 1 aktuelle Phase Wh
[v#35 = ] Kanal 1 Cache kumuliert
[v#36 = ] Kanal 1 Cache letzter Start-Stop

[v#40 REMANENT = 0] Kanal 2 aktiv
[v#41 REMANENT = 0] Kanal 2 Start-Sequenz
[v#42 REMANENT = 0] Kanal 2 kumuliert Wh
[v#43 REMANENT = 0] Kanal 2 letzter Start-Stop Wh
[v#44 REMANENT = 0] Kanal 2 aktuelle Phase Wh
[v#45 = ] Kanal 2 Cache kumuliert
[v#46 = ] Kanal 2 Cache letzter Start-Stop

[v#50 REMANENT = 0] Kanal 3 aktiv
[v#51 REMANENT = 0] Kanal 3 Start-Sequenz
[v#52 REMANENT = 0] Kanal 3 kumuliert Wh
[v#53 REMANENT = 0] Kanal 3 letzter Start-Stop Wh
[v#54 REMANENT = 0] Kanal 3 aktuelle Phase Wh
[v#55 = ] Kanal 3 Cache kumuliert
[v#56 = ] Kanal 3 Cache letzter Start-Stop

[v#60 REMANENT = 0] Kanal 4 aktiv
[v#61 REMANENT = 0] Kanal 4 Start-Sequenz
[v#62 REMANENT = 0] Kanal 4 kumuliert Wh
[v#63 REMANENT = 0] Kanal 4 letzter Start-Stop Wh
[v#64 REMANENT = 0] Kanal 4 aktuelle Phase Wh
[v#65 = ] Kanal 4 Cache kumuliert
[v#66 = ] Kanal 4 Cache letzter Start-Stop

[v#70 REMANENT = 0] Restmenge kumuliert Wh
[v#71 = ] Restmenge Cache kumuliert
###[/DEF]###

###[HELP]###
Version: 1.0

FriWa Wh Verteiler (19100834)

Zweck:
- Verteilt einen monoton steigenden FriWa-Zaehlerstand in Wh auf Zirkulation und vier frei belegbare Kanaele.
- Der FriWa-Zaehlerstand kommt an E1 und darf bei Ueberlauf oder Reset wieder kleiner werden.
- Der Baustein bildet immer nur das Delta zwischen zwei empfangenen Zaehlerstaenden.
- Wenn der neue Zaehlerstand kleiner als der alte ist, wird ein Ruecksprung erkannt. Das Delta wird dann ab 0 mit dem neuen Wert weitergezaehlt. Dadurch geht maximal der nicht uebertragene Rest vor dem Ruecksprung verloren.
- Alle Summen und Phasenwerte werden in remanenten V-Werten gespeichert und ueberstehen einen EDOMI-Neustart.
- Technisch sind diese Werte im DEF als `V# REMANENT` deklariert. Normale `V#` waeren nur RAM-Werte und waeren dafuer nicht ausreichend.

Grundprinzip:
- Es gibt genau einen Zaehlerkontext, dem ein neues Wh-Delta zugeschlagen wird.
- Kanal 1 bis Kanal 4 sind normale Zaehler.
- Zirkulation ist der Hintergrundzaehler und zaehlt nur, wenn kein normaler Kanal aktiv ist und kein normaler Kanal im Nachlauf steht.
- Restmenge zaehlt Wh-Deltas, wenn kein normaler Kanal, keine Zirkulation und kein Nachlauf aktiv ist.
- Bei mehreren aktiven normalen Kanaelen gewinnt der zuletzt gestartete Kanal.
- Wenn der zuletzt gestartete Kanal stoppt und ein frueher gestarteter Kanal noch aktiv ist, zaehlt der Baustein danach wieder fuer diesen frueheren Kanal.

Eingangsgruppen:
- Zirkulation: E10 Eingang, E11 Startwert, E12 Stopwert, E13 Live-Ausgabe, E14 Reset.
- Kanal 1: E19 Name, E20 Eingang, E21 Startwert, E22 Stopwert, E23 Live-Ausgabe, E24 Reset.
- Kanal 2: E29 Name, E30 Eingang, E31 Startwert, E32 Stopwert, E33 Live-Ausgabe, E34 Reset.
- Kanal 3: E39 Name, E40 Eingang, E41 Startwert, E42 Stopwert, E43 Live-Ausgabe, E44 Reset.
- Kanal 4: E49 Name, E50 Eingang, E51 Startwert, E52 Stopwert, E53 Live-Ausgabe, E54 Reset.
- Die Name-Eingaenge sind reine Notizfelder und werden von der Logik nicht ausgewertet.

Start/Stop-Werte:
- Jeder Kanal hat einen Eingang und zwei Sollwerte.
- Standard ist Startwert 1 und Stopwert 0.
- Andere Werte sind erlaubt, z. B. Eingang 27 startet, Eingang 0 stoppt.
- Der Vergleich erfolgt numerisch, wenn beide Werte numerisch sind, sonst als Textvergleich.
- Wenn Startwert und Stopwert gleich sind, gewinnt Start. Diese Einstellung ist nicht sinnvoll und wird im Debug sichtbar.

Nachlauf:
- E2 legt fest, wie lange nach einem Stop noch auf den letzten Wh-Zaehlerstand gewartet wird. Standard: 30 Sekunden.
- Der Nachlauf wird nur genutzt, wenn nach dem Stop kein normaler Kanal aktiv ist.
- Kommt waehrend des Nachlaufs ein normaler Kanal auf Start, zaehlt der neue Kanal sofort.
- Ist nach Stop eines normalen Kanals ein anderer normaler Kanal noch aktiv, wird sofort auf diesen zurueckgeschaltet.
- Erst wenn kein normaler Kanal aktiv ist und kein Nachlauf mehr laeuft, darf Zirkulation wieder zaehlen.

Beispiele:
- Zirkulation aktiv, kein Kanal aktiv: neue Wh-Deltas gehen auf A10.
- Kanal 1 startet: Zirkulation wird unterbrochen, neue Wh-Deltas gehen auf A20.
- Kanal 2 startet waehrend Kanal 1 aktiv ist: Kanal 1 pausiert, Kanal 2 zaehlt.
- Kanal 2 stoppt, Kanal 1 ist weiterhin aktiv: Kanal 1 zaehlt weiter.
- Kanal 1 stoppt, kein anderer normaler Kanal ist aktiv: Kanal 1 bleibt fuer E2 Sekunden im Nachlauf, danach wird seine letzte Start-Stop-Phase abgeschlossen.

Ausgaben:
- A1 Gesamt gezaehlt Wh: Summe aller vom Baustein verarbeiteten positiven Deltas.
- A2 Letztes Delta Wh: letztes aus E1 berechnetes Delta.
- A3 Ruecksprung erkannt Anzahl: Anzahl der erkannten kleineren Zaehlerstaende.
- A4 Aktiver Zaehler: aus, zirkulation, kanal1, kanal2, kanal3, kanal4 oder restmenge.
- A5 Status: kurzer Klartext zum aktuellen Zustand.
- A6 Debug JSON: kompakte Diagnose mit Rohzustaenden, Sequenzen, Nachlauf und Summen.
- A10/A11: Zirkulation kumuliert und letzter Start-Stop.
- A20/A21 bis A50/A51: Kanal 1 bis 4 kumuliert und letzter Start-Stop.
- A60: Restmenge kumuliert.

Kumuliert und letzter Start-Stop:
- Kumuliert ist die dauerhaft aufaddierte Wh-Summe des Kanals.
- Letzter Start-Stop ist die Summe der letzten Phase dieses Kanals.
- Eine Phase beginnt, wenn der Eingang den Startwert erreicht.
- Eine Phase endet, wenn der Eingang den Stopwert erreicht und kein Nachlauf mehr offen ist.
- Wird ein Kanal durch einen anderen Kanal ueberlagert, bleibt seine Phase offen. Sie laeuft weiter, wenn er spaeter wieder der aktive Kontext wird.

Live-Ausgabe:
- Ist die Live-Ausgabe eines Kanals 1, werden seine Ausgaenge bei jeder relevanten Aenderung aktualisiert.
- Ist die Live-Ausgabe 0, werden seine Ausgaenge nur bei Stop/Phasenabschluss oder Reset aktualisiert.
- Die globalen Ausgaenge A1 bis A6 werden bei Wertwechsel immer aktualisiert.

Reset:
- E3 setzt alle Kanalzaehler, Phasen, Start-Sequenzen, Nachlauf und die globale Delta-Summe zurueck.
- E4 setzt nur die Zaehlerstands-Referenz zurueck. Der naechste E1-Wert wird dann als neuer Startpunkt gespeichert und erzeugt noch kein Delta.
- E5 setzt nur die Restmenge zurueck.
- E14/E24/E34/E44/E54 setzen nur den jeweiligen Kanal zurueck.
- Reset-Eingaenge reagieren auf Refresh; der konkrete Eingangswert ist egal.
- Nicht remanent sind nur Ausgangs- und Debug-Caches. Diese werden beim naechsten Lauf neu aufgebaut und enthalten keine fachlichen Zaehlerstaende.

Wichtige Hinweise:
- Der Baustein braucht E1 als echten Trigger bei jedem neuen FriWa-Zaehlerstand.
- Wenn EDOMI den gleichen Eingangswert mehrfach sendet, entsteht Delta 0 und es wird nichts aufaddiert.
- Ohne bekannten Maximalwert kann ein echter 64-bit-Ueberlauf nicht exakt rekonstruiert werden. Der Baustein erkennt den Ruecksprung und zaehlt ab dem neuen Wert weiter.
- Bei sehr grossen Zaehlerstaenden nutzt PHP intern Float-Arithmetik. Wh-Zaehler im normalen Anlagenbetrieb bleiben praktisch weit unter kritischen Bereichen.
- EXEC ist leer: keine HTTP-, SQL- oder Dateisystemarbeit.
###[/HELP]###

###[LBS]###
<?php
function _fw834_num($v,$d=0.0){ return is_numeric($v)?floatval($v):$d; }
function _fw834_bool($v){ return intval($v)==1?1:0; }
function _fw834_eq($a,$b){
	if (is_numeric($a) && is_numeric($b)) return (floatval($a)==floatval($b));
	return (strval($a)===strval($b));
}
function _fw834_name($ch){
	if ($ch==1) return 'zirkulation';
	if ($ch>=2 && $ch<=5) return 'kanal'.($ch-1);
	if ($ch==6) return 'restmenge';
	return 'aus';
}
function _fw834_base($ch){ return 10+($ch*10); }
function _fw834_out_total($ch){ return ($ch==1)?10:(($ch-1)*10+10); }
function _fw834_out_last($ch){ return _fw834_out_total($ch)+1; }
function _fw834_set_changed($id,$out,$value,$varIdx){
	$old=logic_getVar($id,$varIdx);
	if (strval($old)!==strval($value)) {
		logic_setOutput($id,$out,$value);
		logic_setVar($id,$varIdx,$value);
	}
}
function _fw834_reset_channel($id,$ch){
	$b=_fw834_base($ch);
	logic_setVar($id,$b+0,0);
	logic_setVar($id,$b+1,0);
	logic_setVar($id,$b+2,0);
	logic_setVar($id,$b+3,0);
	logic_setVar($id,$b+4,0);
	logic_setVar($id,$b+5,'');
	logic_setVar($id,$b+6,'');
	logic_setOutput($id,_fw834_out_total($ch),0);
	logic_setOutput($id,_fw834_out_last($ch),0);
	if (intval(logic_getVar($id,4))==$ch) logic_setVar($id,4,0);
	if (intval(logic_getVar($id,5))==$ch) {
		logic_setVar($id,5,0);
		logic_setVar($id,6,0);
	}
}
function _fw834_reset_rest($id){
	logic_setVar($id,70,0);
	logic_setVar($id,71,'');
	logic_setOutput($id,60,0);
}
function _fw834_publish_rest($id,$force){
	$total=logic_getVar($id,70);
	if ($force) {
		logic_setOutput($id,60,$total);
		logic_setVar($id,71,$total);
	} else {
		_fw834_set_changed($id,60,$total,71);
	}
}
function _fw834_publish_channel($id,$ch,$force){
	$b=_fw834_base($ch);
	$total=logic_getVar($id,$b+2);
	$last=logic_getVar($id,$b+3);
	if ($force) {
		logic_setOutput($id,_fw834_out_total($ch),$total);
		logic_setOutput($id,_fw834_out_last($ch),$last);
		logic_setVar($id,$b+5,$total);
		logic_setVar($id,$b+6,$last);
	} else {
		_fw834_set_changed($id,_fw834_out_total($ch),$total,$b+5);
		_fw834_set_changed($id,_fw834_out_last($ch),$last,$b+6);
	}
}
function _fw834_finish_phase($id,$ch,$forceOut){
	if ($ch<1 || $ch>5) return;
	$b=_fw834_base($ch);
	$phase=_fw834_num(logic_getVar($id,$b+4),0);
	logic_setVar($id,$b+3,$phase);
	logic_setVar($id,$b+4,0);
	if ($forceOut) _fw834_publish_channel($id,$ch,true);
}
function _fw834_winner_normal($id){
	$winner=0;
	$winnerSeq=-1;
	for ($ch=2;$ch<=5;$ch++) {
		$b=_fw834_base($ch);
		if (intval(logic_getVar($id,$b+0))==1) {
			$seq=floatval(logic_getVar($id,$b+1));
			if ($seq>$winnerSeq) {
				$winnerSeq=$seq;
				$winner=$ch;
			}
		}
	}
	return $winner;
}
function _fw834_process_channel($id,$E,$ch,$inIdx,$startIdx,$stopIdx,&$stopped){
	if ($E[$inIdx]['refresh']!=1) return;
	$value=$E[$inIdx]['value'];
	$isStart=_fw834_eq($value,$E[$startIdx]['value']);
	$isStop=_fw834_eq($value,$E[$stopIdx]['value']);
	$b=_fw834_base($ch);
	$was=intval(logic_getVar($id,$b+0));
	if ($isStart) {
		if ($was!=1) {
			$seq=floatval(logic_getVar($id,3))+1;
			logic_setVar($id,3,$seq);
			logic_setVar($id,$b+1,$seq);
			logic_setVar($id,$b+4,0);
		}
		logic_setVar($id,$b+0,1);
		return;
	}
	if ($isStop) {
		if ($was==1) $stopped[$ch]=1;
		logic_setVar($id,$b+0,0);
	}
}
function _fw834_debug($id,$selected,$delta,$status,$waitUntil){
	$channels=array();
	for ($ch=1;$ch<=5;$ch++) {
		$b=_fw834_base($ch);
		$channels[_fw834_name($ch)]=array(
			'active'=>intval(logic_getVar($id,$b+0)),
			'seq'=>floatval(logic_getVar($id,$b+1)),
			'total_wh'=>_fw834_num(logic_getVar($id,$b+2),0),
			'last_phase_wh'=>_fw834_num(logic_getVar($id,$b+3),0),
			'current_phase_wh'=>_fw834_num(logic_getVar($id,$b+4),0)
		);
	}
	$channels['restmenge']=array(
		'active'=>($selected==6)?1:0,
		'total_wh'=>_fw834_num(logic_getVar($id,70),0)
	);
	return json_encode(array(
		'selected'=>_fw834_name($selected),
		'delta_wh'=>$delta,
		'total_delta_wh'=>_fw834_num(logic_getVar($id,7),0),
		'last_counter_wh'=>_fw834_num(logic_getVar($id,2),0),
		'counter_initialized'=>intval(logic_getVar($id,1)),
		'rollback_count'=>intval(logic_getVar($id,8)),
		'pending'=>array(
			'channel'=>_fw834_name(intval(logic_getVar($id,5))),
			'until'=>$waitUntil
		),
		'status'=>$status,
		'channels'=>$channels
	));
}

function LB_LBSID($id) {
	if (!($E=logic_getInputs($id))) return;

	$watched=array(1,2,3,4,5,10,11,12,13,14,20,21,22,23,24,30,31,32,33,34,40,41,42,43,44,50,51,52,53,54);
	$hasRefresh=false;
	foreach ($watched as $idx) {
		if ($E[$idx]['refresh']==1) { $hasRefresh=true; break; }
	}
	$timerActive=(intval(logic_getState($id))==1);
	if (!$hasRefresh && !$timerActive) return;

	$now=time();
	$wait=max(0,intval(_fw834_num($E[2]['value'],30)));
	$status='bereit';
	$delta=0.0;
	$forcePublish=array(1=>false,2=>false,3=>false,4=>false,5=>false);

	if ($E[3]['refresh']==1) {
		for ($ch=1;$ch<=5;$ch++) {
			_fw834_reset_channel($id,$ch);
			$forcePublish[$ch]=true;
		}
		logic_setVar($id,3,0);
		logic_setVar($id,4,0);
		logic_setVar($id,5,0);
		logic_setVar($id,6,0);
		logic_setVar($id,7,0);
		logic_setVar($id,8,0);
		_fw834_reset_rest($id);
		$status='alle Zaehler zurueckgesetzt';
	}
	if ($E[4]['refresh']==1) {
		logic_setVar($id,1,0);
		logic_setVar($id,2,0);
		$status='Zaehlerstands-Referenz zurueckgesetzt';
	}
	if ($E[5]['refresh']==1) { _fw834_reset_rest($id); }
	if ($E[14]['refresh']==1) { _fw834_reset_channel($id,1); $forcePublish[1]=true; }
	if ($E[24]['refresh']==1) { _fw834_reset_channel($id,2); $forcePublish[2]=true; }
	if ($E[34]['refresh']==1) { _fw834_reset_channel($id,3); $forcePublish[3]=true; }
	if ($E[44]['refresh']==1) { _fw834_reset_channel($id,4); $forcePublish[4]=true; }
	if ($E[54]['refresh']==1) { _fw834_reset_channel($id,5); $forcePublish[5]=true; }

	$stopped=array();
	_fw834_process_channel($id,$E,1,10,11,12,$stopped);
	_fw834_process_channel($id,$E,2,20,21,22,$stopped);
	_fw834_process_channel($id,$E,3,30,31,32,$stopped);
	_fw834_process_channel($id,$E,4,40,41,42,$stopped);
	_fw834_process_channel($id,$E,5,50,51,52,$stopped);

	$pending=intval(logic_getVar($id,5));
	$pendingUntil=intval(logic_getVar($id,6));
	if ($pending>0 && $pendingUntil>0 && $now>=$pendingUntil) {
		$endedPending=$pending;
		_fw834_finish_phase($id,$pending,!_fw834_bool($E[($pending==1)?13:(($pending-1)*10+13)]['value']));
		logic_setVar($id,5,0);
		logic_setVar($id,6,0);
		$pending=0;
		$pendingUntil=0;
		$status='Nachlauf beendet: '._fw834_name($endedPending);
	}

	$oldSelected=intval(logic_getVar($id,4));
	$winner=_fw834_winner_normal($id);
	$selected=0;
	if ($winner>0) {
		$pending=intval(logic_getVar($id,5));
		if ($pending>0 && $pending!=$winner) {
			$liveIdx=($pending==1)?13:(($pending-1)*10+13);
			_fw834_finish_phase($id,$pending,!_fw834_bool($E[$liveIdx]['value']));
		}
		$selected=$winner;
		logic_setVar($id,5,0);
		logic_setVar($id,6,0);
	} else {
		$stoppedOld=($oldSelected>0 && isset($stopped[$oldSelected]));
		if ($stoppedOld && $wait>0) {
			$selected=$oldSelected;
			$pendingUntil=$now+$wait;
			logic_setVar($id,5,$selected);
			logic_setVar($id,6,$pendingUntil);
		} else {
			$pending=intval(logic_getVar($id,5));
			$pendingUntil=intval(logic_getVar($id,6));
			if ($pending>0 && $pendingUntil>$now) {
				$selected=$pending;
			} elseif (intval(logic_getVar($id,20))==1) {
				$selected=1;
			} else {
				$selected=6;
			}
		}
	}

	for ($ch=1;$ch<=5;$ch++) {
		if (isset($stopped[$ch]) && $ch!=$selected) {
			$liveIdx=($ch==1)?13:(($ch-1)*10+13);
			_fw834_finish_phase($id,$ch,!_fw834_bool($E[$liveIdx]['value']));
		}
	}

	if ($E[1]['refresh']==1 && is_numeric($E[1]['value'])) {
		$current=_fw834_num($E[1]['value'],0);
		if (intval(logic_getVar($id,1))!=1) {
			logic_setVar($id,1,1);
			logic_setVar($id,2,$current);
			$status='Zaehlerstands-Referenz gesetzt';
		} else {
			$last=_fw834_num(logic_getVar($id,2),0);
			if ($current>=$last) {
				$delta=$current-$last;
			} else {
				$delta=$current;
				logic_setVar($id,8,intval(logic_getVar($id,8))+1);
				$status='Ruecksprung erkannt, ab 0 weitergezaehlt';
			}
			logic_setVar($id,2,$current);
			if ($delta>0) {
				logic_setVar($id,7,_fw834_num(logic_getVar($id,7),0)+$delta);
				if ($selected>0 && $selected<6) {
					$b=_fw834_base($selected);
					logic_setVar($id,$b+2,_fw834_num(logic_getVar($id,$b+2),0)+$delta);
					logic_setVar($id,$b+4,_fw834_num(logic_getVar($id,$b+4),0)+$delta);
					$status='Delta '.$delta.' Wh auf '._fw834_name($selected);
				} elseif ($selected==6) {
					logic_setVar($id,70,_fw834_num(logic_getVar($id,70),0)+$delta);
					$status='Delta '.$delta.' Wh auf Restmenge';
				} else {
					$status='Delta '.$delta.' Wh ohne aktiven Zaehler';
				}
			}
		}
	}

	logic_setVar($id,4,$selected);

	for ($ch=1;$ch<=5;$ch++) {
		$liveIdx=($ch==1)?13:(($ch-1)*10+13);
		if (_fw834_bool($E[$liveIdx]['value']) || $forcePublish[$ch]) {
			_fw834_publish_channel($id,$ch,false);
		}
	}
	_fw834_publish_rest($id,false);

	$activeName=_fw834_name($selected);
	$waitUntil=intval(logic_getVar($id,6));
	if ($selected>0 && $waitUntil>$now && intval(logic_getVar($id,5))==$selected) {
		$status.=' (Nachlauf bis '.$waitUntil.')';
	}

	_fw834_set_changed($id,1,_fw834_num(logic_getVar($id,7),0),13);
	_fw834_set_changed($id,2,$delta,12);
	_fw834_set_changed($id,3,intval(logic_getVar($id,8)),14);
	_fw834_set_changed($id,4,$activeName,11);
	_fw834_set_changed($id,5,$status,9);
	_fw834_set_changed($id,6,_fw834_debug($id,$selected,$delta,$status,$waitUntil),10);

	if (intval(logic_getVar($id,5))>0 && intval(logic_getVar($id,6))>$now) {
		$delay=max(1000,(intval(logic_getVar($id,6))-$now)*1000);
		logic_setState($id,1,$delay);
	} else {
		logic_setState($id,0);
	}
}
?>
###[/LBS]###

###[EXEC]###
<?
?>
###[/EXEC]###
