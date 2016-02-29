<?php
/**
* Telegram Bot example for Strutture Ricettive Lic. IoDL2.0
* @author Francesco Piero Paolicelli @piersoft
*/
//include("settings_t.php");
include("Telegram.php");

class mainloop{
const MAX_LENGTH = 4096;
function start($telegram,$update)
{

	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");

	$text = $update["message"] ["text"];
	$chat_id = $update["message"] ["chat"]["id"];
	$user_id=$update["message"]["from"]["id"];
	$location=$update["message"]["location"];
	$reply_to_msg=$update["message"]["reply_to_message"];

	$this->shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg);
	$db = NULL;

}

 function shell($telegram,$text,$chat_id,$user_id,$location,$reply_to_msg)
{
	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");
	if (strpos($text,'@ricettivitapugliabot') !== false) $text=str_replace("@ricettivitapugliabot ","",$text);

	if ($text == "/start" || $text == "Informazioni") {
		$img = curl_file_create('puglia.png','image/png');
		$contentp = array('chat_id' => $chat_id, 'photo' => $img);
		$telegram->sendPhoto($contentp);

		$reply = "Benvenuto. Per ricercare una struttura ricettiva della Puglia, censita da ARET Pugliapromozione, digita il nome del Comune oppure clicca sulla graffetta (📎) e poi 'posizione' . Puoi anche ricercare per parola chiave nel titolo anteponendo il carattere ?. Verrà interrogato il DataBase openData utilizzabile con licenza IoDL2.0 presente su http://www.dataset.puglia.it/dataset/elenco-strutture-ricettive . In qualsiasi momento scrivendo /start ti ripeterò questo messaggio di benvenuto.\nQuesto bot, non ufficiale e non collegato con il marchio regionale ViaggiareinPuglia.it, è stato realizzato da @piersoft e potete migliorare il codice sorgente con licenza MIT che trovate su https://github.com/piersoft/ricettivitapugliabot. La propria posizione viene ricercata grazie al geocoder di openStreetMap con Lic. odbl.";
		$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);
		$log=$today. ";new chat started;" .$chat_id. "\n";
		$this->create_keyboard_temp($telegram,$chat_id);

		exit;
		}
		elseif ($text == "Città") {
			$reply = "Digita direttamente il nome del Comune.";
			$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
			$log=$today. ";new chat started;" .$chat_id. "\n";
			exit;
			}
			elseif ($text == "Ricerca") {
				$reply = "Scrivi la parola da cercare anteponendo il carattere ?, ad esempio: ?Astor";
				$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
				$log=$today. ";new chat started;" .$chat_id. "\n";
				exit;
			}
			elseif($location!=null)
		{

			$this->location_manager($telegram,$user_id,$chat_id,$location);
			exit;
		}

		elseif(strpos($text,'/') === false){
			$string=0;

			if(strpos($text,'?') !== false){
				$text=str_replace("?","",$text);
				$location="Sto cercando le strutture aventi nel titolo: ".$text;
				$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
				$string=1;
	//			sleep (1);
			}else{
				$location="Sto cercando le strutture ricettive per località : ".$text;
				$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);
				$string=0;
		//		sleep (1);
			}
			$urlgd="db/ricettive.csv";

			  $inizio=0;
			  $homepage ="";
			$csv = array_map('str_getcsv',file($urlgd));
	  	$count = 0;

				foreach($csv as $data=>$csv1){
					$count = $count+1;
				}
			if ($count ==0 || $count ==1)
			{
						$location="Nessuna struttura trovato";
						$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
						$telegram->sendMessage($content);
			}



			function decode_entities($textt)
			{

							$textt=htmlentities($textt, ENT_COMPAT,'ISO-8859-1', true);
						$textt= preg_replace('/&#(\d+);/me',"chr(\\1)",$textt); #decimal notation
							$textt= preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)",$textt);  #hex notation
						$textt= html_entity_decode($textt,ENT_COMPAT,"UTF-8"); #NOTE: UTF-8 does not work!

							return $textt;
			}

			$result=0;
			$ciclo=0;

//if ($count > 40) $count=40;

$text=str_replace("ò","%C3%B2",$text);
$text=str_replace("à","%C3%A0",$text);
$text=str_replace("è","%C3%A8",$text);
$text=str_replace("é","%C3%A9",$text);
$text=str_replace("ì","%C3%AC",$text);
$text=str_replace("ù","%C3%B9",$text);

  for ($i=$inizio;$i<$count;$i++){


		if ($string==1) {
			$filter= strtoupper($csv[$i][0]);
		}else{
			$filter=strtoupper($csv[$i][10]);
		}



if (strpos(decode_entities($filter),strtoupper($text)) !== false ){
				$ciclo++;
//	if ($ciclo >40) exit;


				$result=1;
				$homepage .="\nID: /".$i."\n";
				$homepage .="Nome: ".decode_entities($csv[$i][0])."\n";
				$homepage .="Tipologia: ".decode_entities($csv[$i][1])." ".decode_entities($csv[$i][2])."\n";
				$homepage .="____________";
				}
				if ($ciclo >400) {
					$location="Troppi risultati per essere visualizzati (più di 400). Restringi la ricerca";
					$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
					$telegram->sendMessage($content);

					 exit;
				}
				}

		$chunks = str_split($homepage, self::MAX_LENGTH);
		foreach($chunks as $chunk) {
		$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);

		}
		$content = array('chat_id' => $chat_id, 'text' => "Clicca sull'ID per i dettagli sulla struttura",'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);


	}else{
		$urlgd="db/ricettive.csv";
  	$text=str_replace("/","",$text);
		$i=intval($text);
  	$inizio=0;
  	$homepage ="";
		$csv = array_map('str_getcsv',file($urlgd));
		$count = 0;
/*
			foreach($csv as $data=>$csv1){
				$count = $count+1;
			}
		if ($count ==0 || $count ==1)
		{
					$location="Nessuna struttura trovato";
					$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
					$telegram->sendMessage($content);
		}

*/

		function decode_entities($textt)
		{

						$textt=htmlentities($textt, ENT_COMPAT,'ISO-8859-1', true);
					$textt= preg_replace('/&#(\d+);/me',"chr(\\1)",$textt); #decimal notation
						$textt= preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)",$textt);  #hex notation
					$textt= html_entity_decode($textt,ENT_COMPAT,"UTF-8"); #NOTE: UTF-8 does not work!

						return $textt;
		}

		$result=0;
		$ciclo=0;

//if ($count > 40) $count=40;





			$result=1;
		//	$homepage .="\n/".$i."\n";
			$homepage .="Nome: ".decode_entities($csv[$i][0])."\n";
			$homepage .="Tipologia: ".decode_entities($csv[$i][1])." ".decode_entities($csv[$i][2])."\n";
			if($csv[$i][6] !=NULL) $homepage .="Indirizzo: ".decode_entities($csv[$i][6]);
			//if($csv[$i][5] !=NULL)	$homepage .=", ".decode_entities($csv[$i][5]);
			$homepage .="\n";
			if($csv[$i][10] !=NULL)$homepage .="Comune: ".decode_entities($csv[$i][10])."\n";
			if($csv[$i][16] !=NULL)$homepage .="Web: ".decode_entities($csv[$i][16])."\n";
			if($csv[$i][17] !=NULL)	$homepage .="Email: ".decode_entities($csv[$i][17])."\n";
		//	if($csv[$i][22] !=NULL)	$homepage .="Descrizione: ".substr(decode_entities($csv[$i][22]), 0, 400)."..[....]\n";
			if($csv[$i][14] !=NULL)	$homepage .="Tel: ".decode_entities($csv[$i][14])."\n";
			if($csv[$i][19] !=NULL)	$homepage .="Servizi: ".decode_entities($csv[$i][19])."\n";
			if($csv[$i][20] !=NULL)	$homepage .="Servizi camera: ".decode_entities($csv[$i][20])."\n";
			if($csv[$i][21] !=NULL)	$homepage .="Prezzo a/s: ".decode_entities($csv[$i][21])."\n";
			if($csv[$i][22] !=NULL)	$homepage .="Prezzo a/s: ".decode_entities($csv[$i][22])."\n";
			if($csv[$i][23] !=NULL)	$homepage .="Foto1: ".decode_entities($csv[$i][23])."\n";
			if($csv[$i][24] !=NULL)	$homepage .="Foto1: ".decode_entities($csv[$i][24])."\n";
			if($csv[$i][25] !=NULL)	$homepage .="Foto1: ".decode_entities($csv[$i][25])."\n";
			if($csv[$i][26] !=NULL)	$homepage .="Foto1: ".decode_entities($csv[$i][26])."\n";
			if($csv[$i][27] !=NULL)	$homepage .="Foto1: ".decode_entities($csv[$i][27])."\n";
			if($csv[$i][12] !=NULL){
				$homepage .="Mappa:\n";
				$homepage .= "http://www.openstreetmap.org/?mlat=".$csv[$i][12]."&mlon=".$csv[$i][13]."#map=19/".$csv[$i][12]."/".$csv[$i][13];
			}

			$homepage .="\n____________";




	$chunks = str_split($homepage, self::MAX_LENGTH);
	foreach($chunks as $chunk) {
	$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
	$telegram->sendMessage($content);

	}

	}
	$this->create_keyboard_temp($telegram,$chat_id);

	}

	function create_keyboard_temp($telegram, $chat_id)
	 {
			 $option = array(["Città","Ricerca"],["Informazioni"]);
			 $keyb = $telegram->buildKeyBoard($option, $onetime=false);
			 $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[Digita un Comune, una Ricerca oppure invia la tua posizione tramite la graffetta (📎)]");
			 $telegram->sendMessage($content);
	 }



function location_manager($telegram,$user_id,$chat_id,$location)
	{

			$lon=$location["longitude"];
			$lat=$location["latitude"];
			$r=1;
			$response=$telegram->getData();
			$response=str_replace(" ","%20",$response);

				$reply="http://nominatim.openstreetmap.org/reverse?email=piersoft2@gmail.com&format=json&lat=".$lat."&lon=".$lon."&zoom=18&addressdetails=1";
				$json_string = file_get_contents($reply);
				$parsed_json = json_decode($json_string);
				//var_dump($parsed_json);
				$comune="";
				$temp_c1 =$parsed_json->{'display_name'};

				if ($parsed_json->{'address'}->{'town'}) {
					$temp_c1 .="\nCittà: ".$parsed_json->{'address'}->{'town'};
					$comune .=$parsed_json->{'address'}->{'town'};
				}else 	$comune .=$parsed_json->{'address'}->{'city'};

				if ($parsed_json->{'address'}->{'village'}) $comune .=$parsed_json->{'address'}->{'village'};
				$location="Sto cercando le strutture ricettive contenenti \"".$comune."\" tramite le coordinate che hai inviato: ".$lat.",".$lon;
				$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);

			  $alert="";
			//	echo $comune;
			$urlgd="db/ricettive.csv";

				$inizio=0;
				$homepage ="";
			$csv = array_map('str_getcsv',file($urlgd));
			$count = 0;
				foreach($csv as $data=>$csv1){
					$count = $count+1;
				}
			if ($count ==0 || $count ==1)
			{
						$location="Nessuna struttura trovato";
						$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
						$telegram->sendMessage($content);
			}
			function decode_entities($text)
			{

							$text=htmlentities($text, ENT_COMPAT,'ISO-8859-1', true);
						$text= preg_replace('/&#(\d+);/me',"chr(\\1)",$text); #decimal notation
							$text= preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)",$text);  #hex notation
						$text= html_entity_decode($text,ENT_COMPAT,"UTF-8"); #NOTE: UTF-8 does not work!

							return $text;
			}

			$result=0;

			$ciclo=0;
//if ($count >40) $count=40;
	for ($i=$inizio;$i<$count;$i++){

		$lat10=floatval($csv[$i][13]);
		$long10=floatval($csv[$i][14]);
		$theta = floatval($lon)-floatval($long10);
		$dist =floatval( sin(deg2rad($lat)) * sin(deg2rad($lat10)) +  cos(deg2rad($lat)) * cos(deg2rad($lat10)) * cos(deg2rad($theta)));
		$dist = floatval(acos($dist));
		$dist = floatval(rad2deg($dist));
		$miles = floatval($dist * 60 * 1.1515 * 1.609344);
	//echo $miles;

		if ($miles >=1){
			$data1 =number_format($miles, 2, '.', '');
			$data =number_format($miles, 2, '.', '')." Km";
		} else {
			$data =number_format(($miles*1000), 0, '.', '')." mt";
			$data1 =number_format(($miles*1000), 0, '.', '');
		}
		$csv[$i][100]= array("distance" => "value");

		$csv[$i][100]= $data;



		$filter=strtoupper($csv[$i][10]);

if (strpos(decode_entities($filter),strtoupper($comune)) !== false ){
	$ciclo++;

	$result=1;
	$homepage .="\nID: /".$i."\n";
	$homepage .="Nome: ".decode_entities($csv[$i][0])."\n";
	$homepage .="Tipologia: ".decode_entities($csv[$i][1])." ".decode_entities($csv[$i][2])."\n";
/*
	if($csv[$i][6] !=NULL) $homepage .="Indirizzo: ".decode_entities($csv[$i][6]);
	//if($csv[$i][5] !=NULL)	$homepage .=", ".decode_entities($csv[$i][5]);
	$homepage .="\n";
	if($csv[$i][10] !=NULL)$homepage .="Comune: ".decode_entities($csv[$i][10])."\n";
	if($csv[$i][16] !=NULL)$homepage .="Web: ".decode_entities($csv[$i][16])."\n";
	if($csv[$i][17] !=NULL)	$homepage .="Email: ".decode_entities($csv[$i][17])."\n";
//	if($csv[$i][22] !=NULL)	$homepage .="Descrizione: ".substr(decode_entities($csv[$i][22]), 0, 400)."..[....]\n";
	if($csv[$i][14] !=NULL)	$homepage .="Tel: ".decode_entities($csv[$i][14])."\n";
	if($csv[$i][19] !=NULL)	$homepage .="Servizi: ".decode_entities($csv[$i][19])."\n";
	if($csv[$i][20] !=NULL)	$homepage .="Servizi camera: ".decode_entities($csv[$i][20])."\n";
	if($csv[$i][21] !=NULL)	$homepage .="Prezzo alta/stagione: ".decode_entities($csv[$i][21])."\n";
	if($csv[$i][22] !=NULL)	$homepage .="Prezzo bassa/stagione: ".decode_entities($csv[$i][22])."\n";
	if($csv[$i][23] !=NULL)	$homepage .="Foto1: ".decode_entities($csv[$i][23])."\n";
	if($csv[$i][24] !=NULL)	$homepage .="Foto1: ".decode_entities($csv[$i][24])."\n";
	if($csv[$i][25] !=NULL)	$homepage .="Foto1: ".decode_entities($csv[$i][25])."\n";
	if($csv[$i][26] !=NULL)	$homepage .="Foto1: ".decode_entities($csv[$i][26])."\n";
	if($csv[$i][27] !=NULL)	$homepage .="Foto1: ".decode_entities($csv[$i][27])."\n";
	*/
	if($csv[$i][12] !=NULL){
		$homepage .="Percorso:\n";
	//	$homepage .= "http://www.openstreetmap.org/?mlat=".$csv[$i][12]."&mlon=".$csv[$i][13]."#map=19/".$csv[$i][12]."/".$csv[$i][13];
			$homepage .="http://map.project-osrm.org/?z=14&center=40.351025%2C18.184133&loc=".$lat."%2C".$lon."&loc=".$csv[$i][12]."%2C".$csv[$i][13]."&hl=en&ly=&alt=&df=&srv=";

	}


				$homepage .="\n____________\n";
				}
					if ($ciclo >400) {
						$location="Troppi risultati per essere visualizzati (più di 400). Restringi la ricerca";
						$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
						$telegram->sendMessage($content);

						 exit;
					}
				}

				$chunks = str_split($homepage, self::MAX_LENGTH);
				foreach($chunks as $chunk) {
				$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);

			}
			$content = array('chat_id' => $chat_id, 'text' => "Clicca sull'ID per i dettagli sulla struttura",'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);

			$this->create_keyboard_temp($telegram,$chat_id);

		exit;
	}


}

?>
