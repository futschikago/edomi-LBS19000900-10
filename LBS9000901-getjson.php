###[DEF]###
[name        		= JSON Abfrage v0.0		]

[e#1    trigger    	= Enable    #init=1		]
[e#2    important  	= URL					]
[e#3				= JSON Key				]


[a#1        		= Daten					]



[v#1       =   								]
[v#2 = ]
[v#3 = ]

###[/DEF]###


###[HELP]###
Dieser Baustein dient dazu einen JSON Wert abzufragen.
Der Baustein wird durch den Wert 1 an E1 freigegeben
Der Baustein Triggert wenn E1 1 wird, oder E2 einen wert bekommt.

An E3 wird der zu suchende Schlüssel für den gesuchten wert angegeben.

Wenn der JSON z.B. { "name" : "kabel eins" } ist dann wird an A1 bei E3 "name" der wert "kabel eins" ausgegeben. 

wenn die Abfrage erfolglos ist gibt der Baustein auf A1 "fehler" aus.

E1: Enable
E2: URL zur JSON abfrage
E3: Der Schlüssel nach dem gesucht werden soll

A1: Ergebnis der Abfrage

###[/HELP]###

###[LBS]###
<?
function LB_LBSID($id) {

	if ($E=logic_getInputs($id)) { 							// Eingaenge Einlesen
		if ($E[1]['refresh']==1 || $E[2]['refresh']==1) {
			If ($E[1]['value']==1){
				logic_setVar($id,2,$E[2]['value']);
				logic_setVar($id,3,$E[3]['value']);
				callLogicFunctionExec(LBSID,$id);				// Exec Starten
			}
		}		
	}
}

?>
###[/LBS]###

###[EXEC]###
<?
require(dirname(__FILE__)."/../../../../main/include/php/incl_lbsexec.php");
set_time_limit(15);			//Script soll maximal 15 Sekunden laufen
restore_error_handler();
error_reporting(0);
sql_connect();

$url=logic_getVar($id,2);
$key=logic_getVar($id,3);
$resolve="";
	
	// function zur Abfrage
	function getjson($_abfrage,$_key){
	try {
		$json= file_get_contents($_abfrage);
		$json=json_decode($json);
		return $json->{$_key};
		} catch (Exception $e) {
		return "fehler";
		}
	}
	
	if ($url<>"" && $key<>""){
		$resolve = getjson($url, $key);
	}
	
	if ($resolve <> ""){
		logic_setOutput($id,1,$resolve);
	}
	else {
		logic_setOutput($id,1,"leer");
	}

sql_disconnect();

?>
###[/EXEC]###