###[DEF]###
[name				= Philips TV jointSPACE v0.3	]

[e#1				= Enable						]
[e#2	important	= IP-Adresse					]
[e#3				= Port 				#init=1925	]

[e#4				= Quelle						]
[e#5				= Mute							]
[e#6				= Key Kommando					]
[e#7				= Volume						]
[e#8				= Refresh						]

[a#1		=	Mute								]
[a#2		=	Volume								]
[a#3		=	Source								]
[a#4		=	Kanal								]

[v#1		=										]
[v#2		=										]
[v#3		=										]
[v#4		=										]//refresh E4
[v#5		=										]//refresh E5
[v#6		=										]//refresh E6
[v#7		=										]//refresh E7
[v#8		=										]//refresh E8

###[/DEF]###


###[HELP]###
Dieser Baustein ist für Philips TV Geraete mit der sogenannten JointSPACE schnittstelle.
Über jointSPACE können Parameter gelesen und geschrieben werden.
Details auf folgender seite:
http://jointspace.sourceforge.net/projectdata/documentation/jasonApi/index.html

Auf E2 die IP Adresse des TV Geräts, und auf E3 den jointSPACE Port angeben.
Der Baustein muss zur bearbeitung an E1=1 freigegeben werden.
Der Baustein Triggert bei einem neuen Telegramm an E4-E8, führt 
den Befehl aus durch den er getriggert wurde und aktualisiert seine Ausgänge.

Mein TV Modell ist ein Philips 47PFL7008K/12

evtl. muss jointSPACE erst aktiviert werden:
http://toengel.net/philipsblog/2010/10/30/philips-58pfl9955-jointspace-in-den-2010er-tv-modellen/


E1: Freigabe des Bausteins (z.B. mit dem Hotcheck Baustein 19000101)
E2: IP Adresse des TV Geräts
E3: jointSPACE Port (normalerweise "1925")
E4: der numerische wert für die Quelle (wie unter http://ip-address:1925/1/sources)
E5: 1=Mute 0=unMute
E6: Key befehl der Fernbedienung (http://jointspace.sourceforge.net/projectdata/documentation/jasonApi/1/doc/API-Method-input-key-POST.html)
E7: Lautstärke als zahl 
E8: bei einem wert ungleich 0 werden nur die ausgänge mit aktualwerten refresht
	Die jointSPACE schnittstelle war bei mir sehr instabil wenn die werte zu oft refresht wurden
	das äusserte sich indem keine befehle ankommen und abgehen. Nach bedienung durch die TV Fernbedienung 
	funktioniert es dann wieder, warum das so ist keine ahnung. 
	

A1: bei Mute =1 unMute=0
A2: aktuelle Lautstärke
A3: name der aktuellen Quelle
A4: name des aktuellen Kanals


v0.1 initial Version
v0.2 Überprüfen auf leere telegramme an E5-E7
	 nur bei source "Tuner" Kanal abfragen
v0.3 nach jedem Befehl kurz Pause machen 
	 (evtl stürzzt dann die jointSPACE Schnittstelle nicht so oft ab)
	 Abfragen während das EPG angezeigt wird berücksichtigt.
	 Diverse Codeverschönerungen ;o)
	 
###[/HELP]###


###[LBS]###
<?
function LB_LBSID($id) {
    if ($E=logic_getInputs($id)) {    
        if ($E[1]['value']==1) {
        	if ($E[4]['refresh']==1 || $E[5]['refresh']==1 ||
        		$E[6]['refresh']==1 || $E[7]['refresh']==1 ||
        		$E[8]['refresh']==1) {
        	
	        	if ($E[4]['refresh']==1 && $E[4]['value']==""){
	        		$E[4]['refresh']=0;
	        	}
	        	if ($E[5]['refresh']==1 && $E[5]['value']==""){
	        		$E[5]['refresh']=0;
	        	}
	        	if ($E[6]['refresh']==1 && $E[6]['value']==""){
	        		$E[6]['refresh']=0;
	        	}
	        	if ($E[7]['refresh']==1 && $E[7]['value']==""){
	        		$E[7]['refresh']=0;
	        	}
	        	
	        	logic_setVar($id,4,$E[4]['refresh']);
	        	logic_setVar($id,5,$E[5]['refresh']);
	        	logic_setVar($id,6,$E[6]['refresh']);
	        	logic_setVar($id,7,$E[7]['refresh']);
	        	logic_setVar($id,8,$E[8]['refresh']);
	            callLogicFunctionExec(LBSID,$id);
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
$V = logic_getVars($id);
$ip = $E[2]['value'];
$port = $E[3]['value'];
	
	function HomepageLaden($url, $postdata)
	{
		$agent = "Chrome v1.0 :)";
		$header[] = "Accept: text/vnd.wap.wml,*.*";
		$ch = curl_init($url);
		usleep(10000);
		if ($ch)
		{
			curl_setopt($ch,    CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch,    CURLOPT_USERAGENT, $agent);
			curl_setopt($ch,    CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch,    CURLOPT_FOLLOWLOCATION, 1);

			if (isset($postdata))
			{
				curl_setopt($ch,    CURLOPT_POST, 1);
				curl_setopt($ch,    CURLOPT_POSTFIELDS, $postdata);
			}

			$tmp = curl_exec ($ch);
			curl_close ($ch);
			
		}
		return $tmp;
	}
	
	function senden($id) {
		global $ip, $port, $V, $E;
		if ($V[4]==1) { // Source umschalten
			$_url = "http://".$ip.":".$port."/1/sources/current";
			$_buffer = HomepageLaden($_url, "{\"id\":\"".$E[4]['value']."\"}");
		}
		
		if ($V[5]==1) { // Mute ON/OFF
			$_url = "http://".$ip.":".$port."/1/audio/volume";
			if ($E[5]['value']==0){
				$_buffer = HomepageLaden($_url, "{\"muted\":\""."false"."\"}");
			}
			else{
				$_buffer = HomepageLaden($_url, "{\"muted\":\""."true"."\"}");
			}
		
		}
		if ($V[6]) { // Fernbedienungs Kommando
			$_url = "http://".$ip.":".$port."/1/input/key";
			$_buffer = HomepageLaden($_url, "{\"key\":\"".$E[6]['value']."\"}");
		}
		
		if ($V[7]==1) { // Volume
			$_url = "http://".$ip.":".$port."/1/audio/volume";
			$_buffer = HomepageLaden($_url, "{\"current\":\"".$E[7]['value']."\"}");
		}
		
	}
	
	function lesen($id) {
		global $ip, $port, $V, $E;

		//Volume einlesen
		$_url = "http://".$ip.":".$port."/1/audio/volume";
		$_buffer = HomepageLaden($_url, null);
		$json=json_decode($_buffer);
		if ($json <>''){
			setLogicLinkAusgang($id,2,$json->{'current'});
			if ($json->{'muted'}=="false") {
				setLogicLinkAusgang($id,1,1);
			}else {
				setLogicLinkAusgang($id,1,0);
			}
			$json="";
		}
		
		// Aktuelle Source einlesen
		$_url = "http://".$ip.":".$port."/1/sources/current";
		$_buffer = HomepageLaden($_url, null);
		$json=json_decode($_buffer);
		$_sourceid = "";
		if ($json <>''){
			$_sourceid = $json->{'id'};
			$json="";
			// Aktuellen Source Namen finden
			$_url = "http://".$ip.":".$port."/1/sources";
			$_buffer = HomepageLaden($_url, null);
			$json=json_decode($_buffer);
			if ($json <>''&& $_sourceid <> ""){
				$_sourcename = $json->{$_sourceid}->{'name'};
				setLogicLinkAusgang($id,3,$_sourcename);
				$json="";
			}else {
				setLogicLinkAusgang($id,3,"EPG");
			}
		}
		
		// Aktuelle Kanal einlesen
		if ($_sourceid >= 19 ) {
			setLogicLinkAusgang($id,4,$_sourcename);
		}
		elseif ($_sourceid == ""){
			setLogicLinkAusgang($id,4,"EPG");
		}
		else{
			$_url = "http://".$ip.":".$port."/1/channels/current";
			$_buffer = HomepageLaden($_url, null);
			$json=json_decode($_buffer);
			if ($json <>''){
				$_channelid = (int)$json->{'id'};
				$json="";
				// Aktuellen Kanal Namen finden
				$_url = "http://".$ip.":".$port."/1/channels/".$_channelid;
				$_buffer = HomepageLaden($_url, null);
				$json=json_decode($_buffer);
				if ($json <>''){
					setLogicLinkAusgang($id,4,$json->{'name'});
					$json="";
				}
			}
		}
		
	}
	
	
//Daten senden und lesen
if ($V[4] || $V[5] || $V[6] || $V[7]) {
	senden($id);
	usleep(500000); // 500ms warten auf änderungen
	lesen($id);
}

//nur Daten lesen
elseif ($V[8]<>0) {
	lesen($id);
}

sql_disconnect();
?>
###[/EXEC]###
