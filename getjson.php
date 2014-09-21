<?php

$conn_string = "host=manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com port=5432 dbname=GabiScrape user=root password=ManegerDB";

$db = pg_pconnect($conn_string);

$query="
select * from ".$_GET["company"]."_flights_v where date>'".$_GET["start"]."' and date<'".$_GET["end"]."' and dst='".$_GET["dst"]."'
";
$result = pg_query($db, $query);
$direction_result = pg_query($db, "select * from directions");
$directions=array();
while ($row = pg_fetch_row($direction_result)) { $directions[$row[0]] = $row[1]; }
$color=array();
$bcolor=array();
for ($i=0;$i<9;$i+=1) {
$bcolor[$i]="#".(string)dechex(7+$i).(string)dechex(7+$i).(string)dechex($i).(string)dechex($i).(string)dechex(7+$i).(string)dechex(7+$i);
}
$i=0;
$destinations=pg_query($db, "select * from destinations");
$tmp_dst=$destinations;
while ($row = pg_fetch_row($tmp_dst)){
 if ($row[0]=="wizz") {
  $color[$row[1]."c"]=$bcolor[$i];
  $color[$row[1]."b"]=$bcolor[$i];
  $i+=1;
 }
}

for ($i=0;$i<9;$i+=1) {
$bcolor[$i]="#".(string)dechex(7+$i).(string)dechex(7+$i).(string)dechex(3+$i).(string)dechex(3+$i).(string)dechex($i).(string)dechex($i);
}
$i=0;
$destinations=pg_query($db, "select * from destinations");
$tmp_dst=$destinations;
while ($row = pg_fetch_row($tmp_dst)){
 if ($row[0]=="easyjet") {
  $color[$row[1]."c"]=$bcolor[$i];
  $color[$row[1]."b"]=$bcolor[$i];
  $i+=1;
 }
}

$json="[";
$first=1;
while ($row = pg_fetch_row($result)) {
 $direction = "";
 if ($first == 1) { $json=$json."{"; $first=0; } else {$json=$json.", {"; }
 $tip="Dep: " . $row[5] . "<BR>Arr: " . $row[6];
 $json=$json.'"textColor": "#fff", "bordercolor":"'.$color[$row[2]."b"].'", "color": "'.$color[$row[2]."c"].'", "allday": "true", "title": "'.$directions[$row[1]].': '.$row[2].' '.$row[3].'", "tooltip":"'.$tip.'", "start": "'.$row[4].'"';
 $json=$json."}";
}
$json=$json."]";
echo $json;
pg_close();
?>

