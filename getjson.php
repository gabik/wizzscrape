<?php

$conn_string = "host=manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com port=5432 dbname=GabiScrape user=root password=ManegerDB";

$db = pg_pconnect($conn_string);

$result = pg_query($db, "select * from ".$_GET["company"]."_flights where date>'".$_GET["start"]."' and date<'".$_GET["end"]."' and dst='".$_GET["dst"]."' and scrape_time=(select max(scrape_time) from ".$_GET["company"]."_flights where dst='".$_GET["dst"]."')");
$direction_result = pg_query($db, "select * from directions");
$directions=array();
while ($row = pg_fetch_row($direction_result)) { $directions[$row[0]] = $row[1]; }
$color=array();
$color['BUDb']="#5173DA";
$color['BUDc']="#99ABEA";
$color['CLJc']="#F55656";
$color['CLJb']="#963636";
$color['KTWc']="#F073E5";
$color['KTWb']="#874081";
$color['PRGc']="#72A9F2";
$color['PRGb']="#3E5D85";
$color['SOFc']="#71F0C3";
$color['SOFb']="#3C8269";
$color['VNOc']="#85F277";
$color['VNOb']="#477A40";
$color['WAWc']="#EBF582";
$color['WAWb']="#535730";
$color['OTPc']="#EDC38C";
$color['OTPb']="#735F45";


$json="[";
$first=1;
while ($row = pg_fetch_row($result)) {
 $direction = "";
 if ($first == 1) { $json=$json."{"; $first=0; } else {$json=$json.", {"; }
 $json=$json.'"textColor": "#000000", "bordercolor":"'.$color[$row[2]."b"].'", "color": "'.$color[$row[2]."c"].'", "allday": "true", "title": "'.$directions[$row[1]].': '.$row[2].' '.$row[3].'", "start": "'.$row[5].'"';
 $json=$json."}";
}
$json=$json."]";
echo $json;

?>

