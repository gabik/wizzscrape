<?php

include 'db_con_string.php';

$db = pg_pconnect($conn_string);
$query="select id from companies where name='".$_POST['company']."'";
$result = pg_query($db, $query);
$company_id_a=pg_fetch_row($result);
$company_id = $company_id_a[0];

$query="
select flights.dst, flights.price, flights.date, flights.dep_time, flights.arr_time, directions.direction, companies.name from flights join directions on flights.direction=directions.id join companies on companies.id=flights.company where date>'".$_POST["start"]."' and date<'".$_POST["end"]."' and dst='".$_POST["dst"]."' and company='$company_id'
";
$result = pg_query($db, $query);

$json="[";
$first=1;
while ($row = pg_fetch_row($result)) {
 if ($first == 1) { $json=$json."{"; $first=0; } else {$json=$json.", {"; }
 $json=$json.'"dst":"'.$row[0].'", "price":"'.$row[1].'","date":"'.$row[2].'","dep_time":"'.$row[3].'","arr_time":"'.$row[4].'","direction":"'.$row[5].'","company":"'.$row[6].'"';
 $json=$json."}";
}
$json=$json."]";
echo $json;
pg_close();
?>

