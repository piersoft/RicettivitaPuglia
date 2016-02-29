<?php
// uso GDRIVE per deterimare dal portale CKAN Regionale l'ultimo CSV aggiornato e lo salvo in locale sul server ogni mezzanotte
$indirizzo ="http://www.dataset.puglia.it/dataset/805bce5d-7a6e-43f0-ace9-b9e97dd5060f/resource/200938d2-c40b-4090-a6ab-c471bc56e46c/download/5593b3c56373760a99010000.csv";
$inizio=1;
$homepage ="";
//  echo $url;
//$csv1 = array_map('str_getcsv', file($indirizzo));
//	$url =$csv1[0][0];

  $homepage1 = file_get_contents($indirizzo);
//	$homepage1=str_replace(",",".",$homepage1); //le lat e lon hanno la , e quindi metto il .
//  $homepage1=str_replace(";",",",$homepage1); // converto il CSV da separatore ; a ,

  //echo $homepage1;
  $file = '/usr/www/piersoft/ricettivitapugliabot/db/ricettive.csv';

// scrivo il contenuto sul file locale.
  file_put_contents($file, $homepage1);
echo "finito";
?>
