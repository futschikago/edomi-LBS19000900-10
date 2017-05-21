###[DEF]###
[name				=	Ambilight jointSPACE v0.0	]

[e#1			 	= Enable						]
[e#2	important 	= IP		#init=192.168.0.167	]
[e#3				= Port 		#init=1925			]
[e#4			 	= Mode 							]
[e#5				= Farbe(RGB)HEX für manual		]

[a#1				= Mode							]
[a#2				= Farbe nur manual Mode			]
[a#3				= Farbe Oben JSON				]
[a#4				= Farbe links JSON				]
[a#5				= Farbe rechts JSON				]

[V#1				= 								]
[V#2				= 								]
[V#3				= 								]
[V#4				= 								]
[V#5				= 								]

###[/DEF]### 


###[HELP]###
#eeeeee
Vorlage: LBS mit EXEC-Script
###[/HELP]###


###[LBS]###
<?

function LB_LBSID($id) {
	if ($E=logic_getInputs($id)) {
		
		if ($E[1]['value']==1) { 	//Freigabe
			if (!logic_getVar($id,1)) {  //Initrun
				logic_callExec(LBSID,$id);
			}
			if ($E[4]['refresh']==1 && $E[4]['value']=="") {
				$E[4]['refresh']=0;
			}
			if ($E[5]['refresh']==1 && $E[5]['value']=="") {
				$E[5]['refresh']=0;
			}
			if ($E[4]['refresh']==1 || $E[5]['refresh']==1) {
				//logic_setVar($id,4,$E[4]['refresh']);
				//logic_setVar($id,5,$E[5]['refresh']);
				//bei Bedarf das EXEC-Script starten:
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

function HomepageLaden($url, $postdata){
	$agent = "Browser v1.0 :)";
	$header[] = "Accept: text/vnd.wap.wml,*.*";
	$ch = curl_init($url);
	//usleep(10000);
	if ($ch){
		curl_setopt($ch,    CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,    CURLOPT_USERAGENT, $agent);
		curl_setopt($ch,    CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch,    CURLOPT_FOLLOWLOCATION, 1);

		if (isset($postdata)){
			curl_setopt($ch,    CURLOPT_POST, 1);
			curl_setopt($ch,    CURLOPT_POSTFIELDS, $postdata);
		}

		$tmp = curl_exec ($ch);
		curl_close ($ch);
			
	}
	return $tmp;
}

function hex2rgb( $colour ) {
	if ( $colour[0] == '#' ) {
		$colour = substr( $colour, 1 );
	}
	if ( strlen( $colour ) == 6 ) {
		list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
	} elseif ( strlen( $colour ) == 3 ) {
		list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
	} else {
		return false;
	}
	$r = hexdec( $r );
	$g = hexdec( $g );
	$b = hexdec( $b );
	return array( 'red' => $r, 'green' => $g, 'blue' => $b );
}

sql_connect();
$E = logic_getInputs($id);
$V = logic_getVars($id);
$ip = $E[2]['value'];
$port = $E[3]['value'];

if (logic_getVar($id,1)) { //Initialsiert??
	if ($E[4]['refresh']==1) {
		if ($V[4]['value']=="internal"||$V[4]['value']=="manual"||$V[4]['value']=="expert") { // Modus umschalten
			$_url = "http://".$ip.":".$port."/1/ambilight/mode";
			$_buffer = HomepageLaden($_url, "{\"current\":\"".$E[4]['value']."\"}");
			$json=json_decode($_buffer);
			if ($json <>''){
				logic_setVar($id,2,$json->{'current'});
				setLogicLinkAusgang($id,1,$json->{'current'});
			}
			else {
				logic_setVar($id,2,"");
				setLogicLinkAusgang($id,1,"");
			}
			$json="";
		}else {
			logic_setVar($id,2,"");
			setLogicLinkAusgang($id,1,"");
		}
	}
	if ($E[5]['refresh']==1 && $V[2]<>"") {
		$rgb = hex2rgb($E[5]['value']);
		if ($rgb) {										//Farbe festlegen
			$_url = "http://".$ip.":".$port."/1/ambilight/cached";
			$_post = "{\"r\":".$rgb[0].",\"g\":".$rgb[1].",\"b\":".$rgb[2]."}";
			$_buffer = HomepageLaden($_url, $_post);
			$json=json_decode($_buffer);
			if ($json <>''){
				setLogicLinkAusgang($id,2,$E[5]['value']);
			}else {
				setLogicLinkAusgang($id,2,"");
			}
			$json="";
		}
	}
}

if (!logic_getVar($id,1)) {
	$_url = "http://".$ip.":".$port."/1/ambilight/mode";
	$_buffer = HomepageLaden($_url, null);
	$json=json_decode($_buffer);
	if ($json <>''){
		logic_setVar($id,2,$json->{'current'});
		setLogicLinkAusgang($id,1,$json->{'current'});
		logic_setVar($id,1,1); // Initialiesierung OK!
	}
	else {
		logic_setVar($id,2,"");
		setLogicLinkAusgang($id,1,"");
	}
	$json="";
}
sql_disconnect();
?>

###[/EXEC]###