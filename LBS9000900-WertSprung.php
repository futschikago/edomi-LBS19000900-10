###[DEF]###
[name		=WertSprung (v0.2)			]

[e#1	trigger		=Wert				]
[e#2	important	=Delta	#init=0	]

[a#1		=Sprg						]
[a#2		=Sprg.Start					]
[a#3		=Sprg.Ziel					]
[a#4		=Sprg.Groesse				]


[v#1		=							] Vergleichswert
###[/DEF]###


###[HELP]###

Dieser Baustein dient dazu einen Wertesprung an E1 zu erkennen.
Wenn der Wert an E1 +/- dem Wert an E2 Springt dann wird A1 auf 1 gesetzt und
A2 auf den Wert an dem der Sprung gestartet ist,
A3 wird auf den Wert des Sprungziels gesetzt.
Auf A4 wird die tatsaechliche Sprung Groesse ausgegeben.
Aendert sich der Wert nach einem Sprung an E1 und wird kein Sprung erkannt,
dann wird A1 wieder auf 0 gesetzt. A2 + A3 + A4 bleiben bis zum naechsten Sprung
unveraendert.

E1: Wert
E2: Sprung delta

A1: Sprung=1 / kein Sprung=0
A2: Sprungstart
A3: Sprungziel
A4: Sprung Groesse
###[/HELP]###


###[LBS]###
<?
function LB_LBSID($id) {

		if ($E=logic_getInputs($id)) { 		// Eingaenge Einlesen

			$V1=logic_getVar($id,1);		// V1 Umladen
			$E1=(int)$E[1]['value'];		// E1 Umladen
			$E2=(int)$E[2]['value'];		// E2 Umladen
			$Hi=$V1+$E2;					// Hi Limit bestimmen
			$Lo=$V1-$E2;					// Low Limit bestimmen

			if ($E[1]['refresh']==1) { 				// Wert hat sich geaendert
				if ($E1 > $Hi || $E1 < $Lo){		// Wert ausserhalb von Sprungfenster
					logic_setOutput($id,1,1);		// Sprung erkannt
					logic_setOutput($id,2,$V1);		// Sprungstart setzen
					logic_setOutput($id,3,$E1);		// Sprungziel setzen
					logic_setOutput($id,4,$E1-$V1);	// Sprunggroesse setzen
				}
				else {
					logic_setOutput($id,1,0);		// Sprung reset
				}
				logic_setVar($id,1,$E1);			// Wert merken
			}
		}
}
?>
###[/LBS]###


###[EXEC]###
<?

?>
###[/EXEC]###
