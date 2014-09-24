<?php

$conn_string = "host=manegerdb.cjjasb6ckbh1.us-east-1.rds.amazonaws.com port=5432 dbname=GabiScrape user=root password=ManegerDB";

$db = pg_pconnect($conn_string);
$query="select id from companies where name='".$_GET['company']."'";
$result = pg_query($db, $query);
$company_id_a=pg_fetch_row($result);
$company_id = $company_id_a[0];

$query=" select * from flights where date>'".$_GET["start"]."' and date<'".$_GET["end"]."' and dst='".$_GET["dst"]."' and company='$company_id' ";
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
$tmp_dst=pg_query($db, "select * from destinations");
while ($row = pg_fetch_row($tmp_dst)){
 if ($row[0]=="wizz") {
  $color[$row[0].$row[1]]=$bcolor[$i];
  $i+=1;
 }
}
pg_result_seek($tmp_dst, 0);

for ($i=0;$i<9;$i+=1) {
$bcolor[$i]="#".(string)dechex(7+$i).(string)dechex(7+$i).(string)dechex(3+$i).(string)dechex(3+$i).(string)dechex($i).(string)dechex($i);
}
$i=0;
while ($row = pg_fetch_row($tmp_dst)){
 if ($row[0]=="easyjet") {
  $color[$row[0].$row[1]]=$bcolor[$i];
  $i+=1;
 }
}
pg_result_seek($tmp_dst, 0);

for ($i=0;$i<5;$i+=1) {
$bcolor[$i]="#".(string)dechex($i*2).(string)dechex($i*2).(string)dechex(7+$i).(string)dechex(7+$i).(string)dechex(11+$i).(string)dechex(11+$i);
}
$i=0;
while ($row = pg_fetch_row($tmp_dst)){
 if ($row[0]=="up") {
  $color[$row[0].$row[1]]=$bcolor[$i];
  $i+=1;
 }
}

$json="[";
$first=1;
while ($row = pg_fetch_row($result)) {
 $direction = "";
 if ($first == 1) { $json=$json."{"; $first=0; } else {$json=$json.", {"; }
 $tip="Dep: " . $row[6] . "<BR>Arr: " . $row[7];
 $json=$json.'"textColor": "#fff", "color":"'.$color[$_GET['company'].$row[3]].'", "allday": "true", "title": "'.$directions[$row[2]].': '.$row[3].' '.$row[4].'", "tooltip":"'.$tip.'", "start": "'.$row[5].'"';
 $json=$json."}";
}
$json=$json."]";
echo $json;
pg_close();
?>

