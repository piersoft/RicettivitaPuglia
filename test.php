<?php

$string=0;
$text="Lecce";
$urlgd="db/luoghi.csv";

  $inizio=0;
  $homepage ="";
$csv = array_map('str_getcsv',file($urlgd));
var_dump($csv[0][1]);
$count = 0;
  foreach($csv as $data=>$csv1){
    $count = $count+1;
  }
if ($count ==0 || $count ==1)
{
  echo "nessun luogo";
  //    $location="Nessun luogo trovato";
  //    $content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
  //    $telegram->sendMessage($content);
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

for ($i=$inizio;$i<$count;$i++){

if ($string==1) {
$filter= strtoupper($csv[$i][0]);
}else{
$filter=strtoupper($csv[$i][3]);
}

if (strpos(decode_entities($filter),strtoupper($text)) !== false ){

  $result=1;
  $homepage .="\n";
  $homepage .="Nome: ".decode_entities($csv[$i][0])."\n";
  $homepage .="Risorsa: ".decode_entities($csv[$i][1])."\n";
  if($csv[$i][4] !=NULL) $homepage .="Indirizzo: ".decode_entities($csv[$i][4]);
  if($csv[$i][5] !=NULL)	$homepage .=", ".decode_entities($csv[$i][5]);
  $homepage .="\n";
  if($csv[$i][3] !=NULL)$homepage .="Comune: ".decode_entities($csv[$i][3])."\n";
  if($csv[$i][9] !=NULL)$homepage .="Web: ".decode_entities($csv[$i][9])."\n";
  if($csv[$i][10] !=NULL)	$homepage .="Email: ".decode_entities($csv[$i][10])."\n";
//	if($csv[$i][22] !=NULL)	$homepage .="Descrizione: ".substr(decode_entities($csv[$i][22]), 0, 400)."..[....]\n";
  if($csv[$i][11] !=NULL)	$homepage .="Tel: ".decode_entities($csv[$i][11])."\n";
  if($csv[$i][14] !=NULL)	$homepage .="Servizi: ".decode_entities($csv[$i][14])."\n";
  if($csv[$i][15] !=NULL)	$homepage .="Attrezzature: ".decode_entities($csv[$i][15])."\n";
  if($csv[$i][16] !=NULL)	$homepage .="Foto1: ".decode_entities($csv[$i][16])."\n";
  if($csv[$i][17] !=NULL) $homepage .="(realizzata da: ".decode_entities($csv[$i][17]).")\n";
  if($csv[$i][18] !=NULL)	$homepage .="Foto2: ".decode_entities($csv[$i][18])."\n";
  if($csv[$i][19] !=NULL) $homepage .="(realizzata da: ".decode_entities($csv[$i][19]).")\n";
  if($csv[$i][7] !=NULL){
    $homepage .="Mappa:\n";
    $homepage .= "http://www.openstreetmap.org/?mlat=".$csv[$i][7]."&mlon=".$csv[$i][8]."#map=19/".$csv[$i][7]."/".$csv[$i][8];
  }

  $homepage .="\n____________\n";
  }
  }
  echo $homepage;
 ?>
