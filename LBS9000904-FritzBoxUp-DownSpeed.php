###[DEF]###
[name			=	FritzBox Inet Speed Monitor v0.0]

[e#1 trigger	= 	Trigger							]
[e#2 important	= 	IP-Adresse FritzBox				]
[e#3			=	Port FritzBox 		#init=49000 ]
[e#4 option		=	Skalierung			#init=0		]	

[a#1			=	Upload 							]
[a#2			=	Download 						]
[a#3			= 	Uhrzeit 						]

###[/DEF]###


###[HELP]###
Dieser Baustein liest die Aktuelle Internet Up und Download Geschwindigkeit der Fritzbox.
UPNP Abfragen müssen aktiviert sein.

E1: Triggert den Baustein bei !=0 Signal werden die Daten gelesen und die Ausgänge aktualisiert.
E2: IP Adresse der Fritzbox
E3: UPNP Port der Fritzbox (standard 49000)
E4: Skalierung, (A1-2 / E4) Standardmäsig gibt der Baustein Byte/s aus (kByte/s = 1000, MByte/s = 1000000).

A1: Aktueller Upload (Byte/s / E4)
A2: Aktueller Download (Byte/s /E4)
A3: Uhrzeit der letzten gelesenen Werte
###[/HELP]###


###[LBS]###
<?
function LB_LBSID($id) {
	if ($E=logic_getInputs($id)) {
	
		//eigener Code...
		if ($E[1]['refresh']==1 ){
			if ($E[1]['value']==1 && $E[2]['value']!=""){
				logic_callExec(LBSID,$id);
			}
		}
	}
}
?>
###[/LBS]###


###[EXEC]###
<?
require(dirname(__FILE__)."/../../../../main/include/php/incl_lbsexec.php");

//bei Bedarf kann hier die maximale Ausführungszeit des Scripts angepasst werden (Default: 30 Sekunden)
//Beispiele:
//set_time_limit(0);	//Script soll unendlich laufen (kein Timeout)
//set_time_limit(60);	//Script soll maximal 60 Sekunden laufen

sql_connect();

$E = getLogicEingangDataAll($id);
$ip = $E[2]['value'];
$port = $E[3]['value'];
$scale = $E[4]['value'];


try {
	// Pollt daten per UPNP aus der Fritzbox
	$client = new SoapClient(
			  null,
			  array(
					'location'   => "http://".$ip.":".$port."/igdupnp/control/WANCommonIFC1",
					'uri'        => "urn:schemas-upnp-org:service:WANCommonInterfaceConfig:1",
					'soapaction' => "",
					'noroot'     => True
			  )
	);
	
	$status = $client->GetCommonLinkProperties();
	$status2 = $client->GetAddonInfos();
	
	$ByteSendRate      = $status2['NewByteSendRate'];
	$ByteReceiveRate   = $status2['NewByteReceiveRate'];
	
	if ($scale > 0){
		setLogicLinkAusgang($id,1,($ByteSendRate/$scale));
		setLogicLinkAusgang($id,2,($ByteReceiveRate/$scale));
		setLogicLinkAusgang($id,3,date("d-m-Y H:i:s"));
	} else {
		setLogicLinkAusgang($id,1,$ByteSendRate);
		setLogicLinkAusgang($id,2,$ByteReceiveRate);
		setLogicLinkAusgang($id,3,date("d-m-Y H:i:s"));
	}
	} catch (Exception $e) {
	//Exception abgefangen:
		setLogicLinkAusgang($id,1,-1);
		setLogicLinkAusgang($id,2,-1);
		setLogicLinkAusgang($id,3,date("d-m-Y H:i:s"));
	}

sql_disconnect();
?>
###[/EXEC]###
