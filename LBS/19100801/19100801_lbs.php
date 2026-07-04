###[DEF]###
[name = Universal P-Regler 1.0]
[titel = P-Regler 1.0]
[version = 1.0]

[e#1 TRIGGER=Sollwert 						]
[e#2 TRIGGER=Istwert 						]
[e#3 TRIGGER=Hysterese 						]
[e#4 option=Stellwert 						]
[e#5 option=Multiplikator	#init=1		]
[e#6 option=Zusatzstellwert	#init=1		]



[a#1		=Differenz Soll-Ist				]
[a#2		=Differenz Ist-Soll				]
[a#3		=Istwert zu Niedrig				]
[a#4		=Istwert zu Niedrig				]
[a#5		=Stellwert Zusatz  Multi		]
[a#6		=Stellwert	+/- Zusatz			]
[a#7		=Sol/Ist Multiplikator			]

[v#1		=								]Differenz Soll-Ist abzüglich Hysterese
[v#2		=								]Differenz Ist-Soll abzüglich Hysterese
[v#3		=								]Stellwert + Zusatz
[v#4		=								]Differenz Ist/Soll * Multi
###[/DEF]###


###[HELP]###
Version: 1.0

Universal P-Regler (19100801)

Zweck:
- Vergleicht Sollwert (E1) und Istwert (E2) mit Hysterese (E3).
- Liefert Richtungsbits (zu niedrig / zu hoch) und berechnet Stellwertkorrekturen.

Eingänge:
- E1 Sollwert
- E2 Istwert
- E3 Hysterese (Totband)
- E4 Stellwert (Basis)
- E5 Multiplikator (P-Anteil)
- E6 Zusatzstellwert (Offset)

Kernlogik:
1) var1 = (Soll-Ist)-Hysterese
2) var2 = (Ist-Soll)-Hysterese
3) var1>0 => A3=1 (Ist zu niedrig): Stellwert wird erhöht
4) var2>0 => A4=1 (Ist zu hoch): Stellwert wird reduziert

Ausgänge:
- A1/A2: reine Differenzen
- A3/A4: Richtungsbits
- A5: finaler Stellwert inkl. Multiplikator + Zusatz
- A6: Stellwert +/- Zusatz
- A7: Multiplikatoranteil

Hinweis:
- Ausgänge werden nur bei Wertänderung gesendet (_setChanged), um Telegrammflut zu vermeiden.
- E5=0 deaktiviert den proportionalen Anteil effektiv.

###[/HELP]###


###[LBS]###
<?
function _setChanged($id,$out,$val,$varIdx){
	$old = logic_getVar($id,$varIdx);
	$new = is_numeric($val) ? (float)$val : (string)$val;
	$oldN = is_numeric($old) ? (float)$old : (string)$old;
	if($new!==$oldN){
		logic_setOutput($id,$out,$val);
		logic_setVar($id,$varIdx,$val);
	}
}

function LB_LBSID($id) {
	if ($E=logic_getInputs($id)) {
		if ($E[1]['refresh']==1 || $E[2]['refresh']==1 || $E[3]['refresh']==1 || $E[4]['refresh']==1 ||  $E[5]['refresh']==1 || $E[6]['refresh']==1 ) {

			$Sollwert=$E[1]['value']; if (!is_numeric($Sollwert)) {$Sollwert=0;}
			$Istwert=$E[2]['value']; if (!is_numeric($Istwert)) {$Istwert=0;}
			$Hysterese=$E[3]['value']; if (!is_numeric($Hysterese)) {$Hysterese=0;}
			$Stellwert=$E[4]['value']; if (!is_numeric($Stellwert)) {$Stellwert=0;}
			$Multiplikator=$E[5]['value']; if (!is_numeric($Multiplikator)) {$Multiplikator=0;}
			$Zusatzstellwert=$E[6]['value']; if (!is_numeric($Zusatzstellwert)) {$Zusatzstellwert=1;}

			$var1=(($Sollwert-$Istwert)-$Hysterese);
			$var2=(($Istwert-$Sollwert)-$Hysterese);
			logic_setVar($id,1,$var1);
			logic_setVar($id,2,$var2);

			_setChanged($id,1,($Sollwert-$Istwert),21);
			_setChanged($id,2,($Istwert-$Sollwert),22);

			if ($var1 > 0) {
				_setChanged($id,3,1,23);
				$v3 = ($Zusatzstellwert+$Stellwert);
				$v4 = ($Sollwert-$Istwert)*$Multiplikator;
				logic_setVar($id,3,$v3);
				logic_setVar($id,4,$v4);
				_setChanged($id,6,$v3,26);
				_setChanged($id,7,$v4,27);
				_setChanged($id,5,$Stellwert+$v4+$Zusatzstellwert,25);
			} else {
				_setChanged($id,3,0,23);
			}

			if ($var2 > 0) {
				_setChanged($id,4,1,24);
				$v3 = ($Stellwert-$Zusatzstellwert);
				$v4 = ($Istwert-$Sollwert)*$Multiplikator;
				logic_setVar($id,3,$v3);
				logic_setVar($id,4,$v4);
				_setChanged($id,6,$v3,26);
				_setChanged($id,7,$v4,27);
				_setChanged($id,5,$Stellwert-$v4-$Zusatzstellwert,25);
			} else {
				_setChanged($id,4,0,24);
			}
		}
	}
}
?>
###[/LBS]###


###[EXEC]###
<?

?>
###[/EXEC]###
